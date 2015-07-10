<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
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


    private static $_patternName;

    private static $_patternDCAs;

    private static $_patternFileds;


    private function patternName()
    {
        if (isset(static::$_patternName))
        {
            return static::$_patternName;
        }

        $record = \Database::getInstance()
            ->prepare('SELECT type FROM ' . static::$table . ' WHERE id=?')
            ->execute(\Input::get('id'));

        if ($record->next()) {
            static::$_patternName = strpos($record->type, 'ptr_') !== 0 ? null : $record->type;
        }
        else {
            static::$_patternName = null;
        }

        return static::$_patternName;
    }


    private function patternDCA($patternName)
    {
        if (isset(static::$_patternDCAs[$patternName]))
        {
            return static::$_patternDCAs[$patternName];
        }

        static::$_patternDCAs[$patternName] = $this->parsedDCA($patternName) ?: null;

        return static::$_patternDCAs[$patternName];
    }


    private function patternFields($patternName)
    {
        if (isset(static::$_patternFileds))
        {
            return static::$_patternFileds;
        }

        $dca = $this->patternDCA($patternName);
        static::$_patternFileds = $dca['fields'] ?: array();

        return static::$_patternFileds;
    }



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
        foreach($data as $dataFieldName=>$fieldValue)
        {
            $fieldName = str_replace(static::$fieldPrefix, '', $dataFieldName);
            $type = static::$templatesData[$strTemplate]['fields'][$fieldName]['inputType'];

            if (static::$templatesData[$strTemplate]['fields'][$fieldName]['translatable'] && $GLOBALS['TRANSLATE_TO_LANGUAGE'])
            {
                $translatedFieldName = $dataFieldName . '_lang_' . $GLOBALS['TRANSLATE_TO_LANGUAGE'];
                $fieldValue = $data[$translatedFieldName];
            }

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
        $arrTemplates = \Carbid\PatternTemplate::getTemplateGroup('ptr_');
        return array_values($arrTemplates);
    }


    /**
     * Generate DCA for variables
     * Called on initializeSystem hook
     */
    public function initializeSystemHook()
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

        if (TL_MODE === 'FE' || !\Input::get('id')) {
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

        $patternName = $this->patternName();
        if (!$patternName) return;

        $patternFields = $this->patternFields($patternName);
        if (!$patternFields) return;

        \Controller::loadDataContainer(static::$table);
    }


    public function loadDataContainerHook($strTable)
    {
        if (($strTable !== 'tl_module' && $strTable !== 'tl_content') || !static::$isEnabled || TL_MODE !== 'BE' || !\Input::get('id'))
        {
            return;
        }
        $patternName = $this->patternName();
        $patternFields = $this->patternFields($patternName);
        $strPalettePart = '';

        foreach ($patternFields as $fieldName=>$objVar)
        {
            if ($objVar['inputType'] == 'group')
            {
                $GLOBALS['TL_LANG'][static::$table][$fieldName] = $objVar['label'][0];
                $strPalettePart .= ';{' . $fieldName . '}';
            }
            else
            {
                $GLOBALS['TL_DCA'][static::$table]['fields'][$fieldName] = $objVar;
                $GLOBALS['TL_DCA'][static::$table]['fields'][$fieldName]['save_callback'][] = array('Carbid\Pattern', 'preventFieldSaving');
                $GLOBALS['TL_DCA'][static::$table]['fields'][$fieldName]['load_callback'][] = array('Carbid\Pattern', 'setVariable');
                $strPalettePart .= ',' . $fieldName;
            }
        }

        if (!empty($strPalettePart))
        {
            $strPalettePart = ';{' . $patternName . '_legend}' . $strPalettePart . ',pattern_data';
        }

        if ($this->isContent())
        {
            $strPalette = '{type_legend},type' . $strPalettePart . ';{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
        }
        else
        {
            $strPalette = '{title_legend},name,type' . $strPalettePart . ';{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
        }

        $GLOBALS['TL_DCA'][static::$table]['palettes'][$patternName] = $strPalette;
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

            $arrVar['inputTypeOriginal'] = $arrVar['inputType'];

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
                    $arrVar['relation'] = array('type'=>'hasOne', 'load'=>'lazy');
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

            if ($arrEval['tl_class'] && $arrVar['eval']['tl_class']) {
                $arrVar['eval']['tl_class'] .= ' ' . $arrEval['tl_class'];
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
                    $strDbField = $objVariable['inputType'];
                    break;
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

        foreach ($GLOBALS['TL_DCA'][static::$table]['fields'] as $fieldName=>$fieldDCA)
        {
            // Continue if not pattern field
            if (strpos($fieldName, 'ptr_field_') !== 0)
            {
                continue;
            }

            $newFieldValue = \Input::post($fieldName);

            switch ($fieldDCA['inputTypeOriginal'])
            {
                case 'text':
                case 'textarea':
                    //if ($objVariable['allowHtml'])
                    //{
                        //$newFieldValue = \Input::stripTags($_POST[$fieldName], \Config::get('allowedTags'));
                    //}
                    break;

                case 'wysiwyg':
                case 'html':
                    $newFieldValue = \Input::stripTags($_POST[$fieldName], \Config::get('allowedTags'));
                    break;

                case 'image':
                case 'file':
                    $newFieldValue = \String::uuidToBin($newFieldValue);
                    break;

                case 'folder':
                    $newFieldValue = \String::uuidToBin($newFieldValue);
                    break;

                case 'date':
                    if (!empty($newFieldValue))
                    {
                        $objDate = new \Date($newFieldValue, \Config::get('dateFormat'));
                        $newFieldValue = $objDate->tstamp;
                    }
                    break;

                case 'time':
                    if (!empty($newFieldValue))
                    {
                        $objDate = new \Date($newFieldValue, \Config::get('timeFormat'));
                        $newFieldValue = $objDate->tstamp;
                    }
                    break;

                case 'datetime':
                    if (!empty($newFieldValue))
                    {
                        $objDate = new \Date($newFieldValue, \Config::get('datimFormat'));
                        $newFieldValue = $objDate->tstamp;
                    }
                    break;
            }
            $data[$fieldName] = $newFieldValue;
        }

        return serialize($data);
    }

}
