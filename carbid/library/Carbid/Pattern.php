<?php

/**
 * Pattern for Contao Open Source CMS
 *
 * Copyright (C) 2014 Alexander Stulnikov
 *
 * @package    Pattern
 * @link       https://github.com/alarstyle/contao-pattern
 * @license    http://opensource.org/licenses/MIT
 */


namespace Carbid;


/**
 * Class Pattern
 */
class Pattern extends \Controller
{

    /**
     * @var array Storing templates data
     */
    protected static $templatesData = array();

    /**
     * Storing variables array for backend editing
     */
    protected static $arrVariables = array();


    /**
     * Current table (content or module)
     */
    protected static $table;


    /**
     * true if current page need to be proceed with pattern methods
     */
    protected static $isEnabled = false;


    protected static $fieldPrefix = 'ptr_field_';



    /**
     * @return bool
     */
    protected function isContent()
    {
        return static::$table === \ContentModel::getTable();
    }


    /**
     * @return bool
     */
    protected function isModule()
    {
        return static::$table === \ModuleModel::getTable();
    }


    public static function parseData($data, $strTemplate)
    {
        $data = deserialize($data, true);

        $newArr = array();
        foreach($data as $fieldName=>$fieldValue)
        {
            $fieldName = str_replace(static::$fieldPrefix, '', $fieldName);
            $type = static::$templatesData[$strTemplate]['fields'][$fieldName]['inputType'];
            switch($type)
            {
                case 'image':
                case 'file':
                case 'folder':
                    $objModel = \FilesModel::findByUuid($fieldValue);
                    $newArr[$fieldName] = $objModel->path;
                    break;

                default:
                    $newArr[$fieldName] = $fieldValue;
                    break;
            }
        }

        return $newArr;
    }


    /**
     * Return all pattern templates as array
     *
     * @return array
     */
    public function getPatternTemplates()
    {
        $arrTemplates = \Pattern\PatternTemplate::getTemplateGroup('ptr_');
        return array_values($arrTemplates);
    }


    /**
     * Generate DCA for variables
     * Called on initializeSystem hook
     */
    public function initializeSystem()
    {

        // Load pattern data files
        foreach( glob(TL_ROOT . '/templates/ptr_*.php') as $strFile )
        {
            static::$templatesData[ basename($strFile, '.php') ] = include $strFile;
        }

        // Load pattern templates
        $templates = $this->getPatternTemplates();
        foreach( $templates as $templateName )
        {
            if (!static::$templatesData[$templateName]['disableContent'])
            {
                $GLOBALS['TL_CTE'][static::$templatesData[$templateName]['contentCategory'] ?: 'patterns'][$templateName] = '\Carbid\ContentPattern';
            }
            if (!static::$templatesData[$templateName]['disableModule'])
            {
                $GLOBALS['FE_MOD'][static::$templatesData[$templateName]['moduleCategory'] ?: 'patterns'][$templateName] = '\Carbid\ModulePattern';
            }
        }

        if (TL_MODE == 'FE'){
            return;
        }


        static::$table = \Input::get('table');

        static::$isEnabled = ($this->isContent() || $this->isModule()) && (\Input::get('act') === 'edit' || TL_SCRIPT === 'contao/file.php');

        if (!static::$isEnabled)
        {
            return;
        }

        $this->import('BackendUser', 'User');
        $this->User->authenticate();

        $objElement = $this->isContent() ? \ContentModel::findByPk(\Input::get('id')) : \ModuleModel::findByPk(\Input::get('id'));

        if (empty($objElement) || strpos($objElement->type, 'ptr_') !== 0 )
        {
            return;
        }

        $arrData = $this->parsedDCA($objElement->type);

        static::$arrVariables = $arrData['fields'] ?: array();

        \Controller::loadDataContainer(static::$table);

        $strFields = '';

        foreach (static::$arrVariables as $key=>$objVar)
        {
            if ($objVar['inputType'] == 'group')
            {
                $strFields .= ';{' . $key . '}';
                $GLOBALS['TL_LANG'][static::$table][$key] = $objVar['label'][0];
            }
            else
            {
                $strFields .= ',' . $key;
                $GLOBALS['TL_DCA'][static::$table]['fields'][$key] = $objVar;
                $GLOBALS['TL_DCA'][static::$table]['fields'][$key]['save_callback'][] = array('Carbid\Pattern', 'preventFieldSaving');
                $GLOBALS['TL_DCA'][static::$table]['fields'][$key]['load_callback'][] = array('Carbid\Pattern', 'setVariable');
            }
        }

        if (!empty($strFields))
        {
            $strFields = ';{' . $objElement->type . '_legend}' . $strFields;
        }

        if ($this->isContent())
        {
            $strPalette = '{type_legend},type' . $strFields . ';{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
        }
        else
        {
            $strPalette = '{title_legend},name,type' . $strFields . ';{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
        }

        $GLOBALS['TL_DCA'][static::$table]['palettes'][$objElement->type] = $strPalette . ',pattern_data';

    }


    public function loadLanguageFileHook($name, $language)
    {
        if ($name === 'default')
        {
            foreach( static::$templatesData as $templateName=>$templateData )
            {
                $label = DynamicDCA::getLabelTranslation($templateData['label'], $language);

                $GLOBALS['TL_LANG']['CTE'][$templateName] = $label;
                $GLOBALS['TL_LANG'][\ContentModel::getTable()][$templateName . '_legend'] = $label[0];
                $GLOBALS['TL_LANG']['FMD'][$templateName] = $label;
                $GLOBALS['TL_LANG'][\ModuleModel::getTable()][$templateName . '_legend'] = $label[0];
            }
        }
    }


    public function parsedDCA($templateName)
    {
        $arrData = static::$templatesData[$templateName];

        $palette = '';

        if (empty($arrData))
        {
            return null;
        }

        if (empty($arrData['fields']))
        {
            return $arrData;
        }

        $arrFields = array();
        foreach($arrData['fields'] as $fieldName=>$fieldData){
            $arrFields['ptr_field_' . $fieldName] = $fieldData;
        }
        $arrData['fields'] = $arrFields;

        foreach ($arrData['fields'] as &$arrVar)
        {
            $arrEval = array();

            switch($arrVar['inputType'])
            {
                case 'wysiwyg':
                    $arrVar['inputType'] = 'textarea';
                    $arrEval = array('rte'=>'tinyMCE', 'doNotSaveEmpty'=>true);
                    break;

                case 'html':
                    $arrVar['inputType'] = 'textarea';
                    $arrEval = array('allowHtml'=>true, 'class'=>'monospace', 'rte'=>'ace|html');
                    break;

                case 'checkbox':
                    $arrVar['inputType'] = 'checkbox';
                    break;

                case 'image':
                    $arrVar['inputType'] = 'fileTree';
                    $arrEval = array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>\Config::get('validImageTypes'));
                    break;

                case 'file':
                    $arrVar['inputType'] = 'fileTree';
                    $arrEval = array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true);
                    break;

                case 'folder':
                    $arrVar['inputType'] = 'fileTree';
                    $arrEval = array('fieldType'=>'radio');
                    break;

                case 'page':
                    $arrVar['inputType'] = 'pageTree';
                    $arrVar['foreignKey'] = 'tl_page.title';
                    $arrEval = array('fieldType'=>'radio');
                    $arrVar['relation'] = array('type'=>'hasOne', 'load'=>'eager');
                    break;

                case 'date':
                    $arrVar['inputType'] = 'text';
                    $arrEval = array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'wizard');
                    break;

                case 'time':
                    $arrVar['inputType'] = 'text';
                    $arrEval = array('rgxp'=>'time', 'datepicker'=>true, 'tl_class'=>'wizard');
                    break;

                case 'datetime':
                    $arrVar['inputType'] = 'text';
                    $arrEval = array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'wizard');
                    break;

                case 'color':
                    $arrVar['inputType'] = 'text';
                    $arrEval = array('maxlength'=>6, 'colorpicker'=>true, 'isHexColor'=>true, 'decodeEntities'=>true, 'tl_class'=>'wizard');
                    break;
            }

            $arrVar['eval'] = array_merge($arrEval, $arrVar['eval'] ?: array());

            $arrVar['label'] = DynamicDCA::getLabelTranslation($arrVar['label'], $GLOBALS['TL_LANGUAGE']);
        }

        return $arrData;
    }


    /**
     * Prevent saving variables to content or module database tables.
     * Called on save_callback of fields.
     *
     * @param $varValue
     * @param $dc
     *
     * @return null
     */
    public function preventFieldSaving($varValue, $dc)
    {
        return null;
    }


    /**
     * Set variable value.
     * Called on load_callback of field.
     *
     * @param $varValue
     * @param $dc
     *
     * @return null
     */
    public function setVariable($varValue, $dc)
    {
        if ($_POST || empty(static::$arrVariables))
        {
            return null;
        }

        return static::$arrVariables[$dc->field];
    }


    /**
     * Load variables from database
     * Called on onload_callback of table
     *
     * @param $dc
     */
    public function getVariables($dc)
    {
        if ($dc->table === null) {
            return;
        }
        $value = null;
        $record = \Database::getInstance()
            ->prepare("SELECT pattern_data FROM {$dc->table} WHERE id=?")
            ->execute($dc->id);
        if ($record->next()) {
            $value = $record->pattern_data;
        }
        static::$arrVariables = deserialize($value, true);
    }


    /**
     * Save variables to database
     * Called on onsubmit_callback of table
     *
     * @param $dc
     */
    public function saveVariables($dc)
    {
        if (!static::$isEnabled || strpos($_POST['type'], 'ptr_') !== 0)
        {
            return;
        }

        return;

        $data = array();

        foreach (static::$templatesData[$dc->activeRecord->type]['fields'] as $strVarName=>$objVariable)
        {
            $strVarName = 'ptr_field_' . $strVarName;
            $newValue = \Input::post($strVarName);

            switch ($objVariable['inputType'])
            {
                case 'text':
                case 'textarea':
                    $strDbField = 'textarea';
                    if ($objVariable['allowHtml'])
                    {
                        $newValue = \Input::stripTags($_POST[$newValue], \Config::get('allowedTags'));
                    }
                    break;

                case 'wysiwyg':
                case 'html':
                    $strDbField = 'html';
                    $newValue = \Input::stripTags($_POST[$newValue], \Config::get('allowedTags'));
                    break;

                case 'checkbox':
                    $strDbField = 'checkbox';
                    break;

                case 'image':
                case 'file':
                    $strDbField = 'file';
                    $newValue = \String::uuidToBin($newValue);
                    break;

                case 'folder':
                    $strDbField = 'folder';
                    $newValue = \String::uuidToBin($newValue);
                    break;

                case 'page':
                    $strDbField = 'page';
                    break;

                case 'date':
                    $strDbField = 'date';
                    if (!empty($newValue))
                    {
                        $objDate = new \Date($newValue, \Config::get('dateFormat'));
                        $newValue = $objDate->tstamp;
                    }
                    break;

                case 'time':
                    $strDbField = 'time';
                    if (!empty($newValue))
                    {
                        $objDate = new \Date($newValue, \Config::get('timeFormat'));
                        $newValue = $objDate->tstamp;
                    }
                    break;

                case 'datetime':
                    $strDbField = 'datetime';
                    if (!empty($newValue))
                    {
                        $objDate = new \Date($newValue, \Config::get('datimFormat'));
                        $newValue = $objDate->tstamp;
                    }
                    break;

                case 'color':
                    $strDbField = 'color';
                    break;

                default:
                    continue 2;
            }
            $data[$strVarName] = $newValue;
        }

        $data = serialize($data);

        \Database::getInstance()
            ->prepare("UPDATE $dc->table SET pattern_data = ? WHERE id = ?")
            ->execute($data, $dc->id);
    }


    public function saveData($value, $dc)
    {
        $data = array();

        foreach (static::$templatesData[$dc->activeRecord->type]['fields'] as $strVarName=>$objVariable)
        {
            $strVarName = 'ptr_field_' . $strVarName;
            $newValue = \Input::post($strVarName);

            switch ($objVariable['inputType'])
            {
                case 'text':
                case 'textarea':
                    $strDbField = 'textarea';
                    if ($objVariable['allowHtml'])
                    {
                        $newValue = \Input::stripTags($_POST[$newValue], \Config::get('allowedTags'));
                    }
                    break;

                case 'wysiwyg':
                case 'html':
                    $strDbField = 'html';
                    $newValue = \Input::stripTags($_POST[$newValue], \Config::get('allowedTags'));
                    break;

                case 'checkbox':
                    $strDbField = 'checkbox';
                    break;

                case 'image':
                case 'file':
                    $strDbField = 'file';
                    $newValue = \String::uuidToBin($newValue);
                    break;

                case 'folder':
                    $strDbField = 'folder';
                    $newValue = \String::uuidToBin($newValue);
                    break;

                case 'page':
                    $strDbField = 'page';
                    break;

                case 'date':
                    $strDbField = 'date';
                    if (!empty($newValue))
                    {
                        $objDate = new \Date($newValue, \Config::get('dateFormat'));
                        $newValue = $objDate->tstamp;
                    }
                    break;

                case 'time':
                    $strDbField = 'time';
                    if (!empty($newValue))
                    {
                        $objDate = new \Date($newValue, \Config::get('timeFormat'));
                        $newValue = $objDate->tstamp;
                    }
                    break;

                case 'datetime':
                    $strDbField = 'datetime';
                    if (!empty($newValue))
                    {
                        $objDate = new \Date($newValue, \Config::get('datimFormat'));
                        $newValue = $objDate->tstamp;
                    }
                    break;

                case 'color':
                    $strDbField = 'color';
                    break;

                default:
                    continue 2;
            }
            $data[$strVarName] = $newValue;
        }

        return serialize($data);
    }

}
