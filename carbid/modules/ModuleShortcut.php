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
 * Class ModuleShortcut
 *
 * This module is used to easily access other backend elements.
 */
class ModuleShortcut extends \Contao\BackendModule
{
    /**
     * Template
     * @var string
     */
    //protected $strTemplate = 'be_shop_config';


    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        // Create dummy variable, it is used by Backend class
        $this->Template = new \BackendTemplate('be_main');

        $module    = \Input::get('do');
        $arrModule = $GLOBALS['BE_MOD_SHORTCUT'][$module];

        if (!$arrModule || !$arrModule['shortcut']) {
            $this->log('Module "' . $module . '" has no "shortcut" data ', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        // Originally from tl_content.php
        // Setting dynamic parent table
        if ($arrModule['shortcut']['do'] == 'article' || $arrModule['shortcut']['do'] == 'page')
        {
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_article';
            $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content', 'checkPermission');
        }

        // Hide 'save' buttons except main one if it is shortcut to edit mode
        if ($arrModule['shortcut']['act'] == 'edit')
        {
            $table = \Input::get('table');
            $GLOBALS['TL_DCA'][$table]['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA'][$table]['config']['switchToEdit'] = false;
        }

        // Prevent recursion
        unset($GLOBALS['BE_MOD_SHORTCUT'][$module]['callback']);

        $main = $this->getBackendModule($arrModule['shortcut']['do']);

        //var_dump($this->Template->headline);

        return $main;
    }

    /**
     * This method will not be called
     */
    protected function compile()
    {
        return '';
    }

    /**
     * Returns backend module array from the $GLOBALS['BE_MOD']
     * @param string
     * @return array
     */
    public static function &getBackendModuleArr($module)
    {
        foreach ($GLOBALS['BE_MOD'] as &$arrGroup)
        {
            if (isset($arrGroup[$module]))
            {
                return $arrGroup[$module];
            }
        }
        return array();
    }


    protected function getBackendModule($module)
    {
        $arrModule = array();

        foreach ($GLOBALS['BE_MOD'] as &$arrGroup)
        {
            if (isset($arrGroup[$module]))
            {
                $arrModule =& $arrGroup[$module];
                break;
            }
        }

        $arrInactiveModules = \ModuleLoader::getDisabled();

        // Check whether the module is active
        if (is_array($arrInactiveModules) && in_array($module, $arrInactiveModules))
        {
            $this->log('Attempt to access the inactive back end module "' . $module . '"', __METHOD__, TL_ACCESS);
            $this->redirect('contao/main.php?act=error');
        }

        $this->import('BackendUser', 'User');

        $strTable = \Input::get('table') ?: $arrModule['tables'][0];
        $id = (!\Input::get('act') && \Input::get('id')) ? \Input::get('id') : $this->Session->get('CURRENT_ID');

        // Store the current ID in the current session
        if ($id != $this->Session->get('CURRENT_ID'))
        {
            $this->Session->set('CURRENT_ID', $id);
        }

        define('CURRENT_ID', (\Input::get('table') ? $id : \Input::get('id')));
        $this->Template->headline = $GLOBALS['TL_LANG']['MOD'][$module][0];

        // Add the module style sheet
        if (isset($arrModule['stylesheet']))
        {
            foreach ((array) $arrModule['stylesheet'] as $stylesheet)
            {
                $GLOBALS['TL_CSS'][] = $stylesheet;
            }
        }

        // Add module javascript
        if (isset($arrModule['javascript']))
        {
            foreach ((array) $arrModule['javascript'] as $javascript)
            {
                $GLOBALS['TL_JAVASCRIPT'][] = $javascript;
            }
        }

        $dc = null;

        // Redirect if the current table does not belong to the current module
        if ($strTable != '')
        {
            if (!in_array($strTable, (array)$arrModule['tables']))
            {
                $this->log('Table "' . $strTable . '" is not allowed in module "' . $module . '"', __METHOD__, TL_ERROR);
                $this->redirect('contao/main.php?act=error');
            }

            // Load the language and DCA file
            \System::loadLanguageFile($strTable);
            $this->loadDataContainer($strTable);

            // Include all excluded fields which are allowed for the current user
            if ($GLOBALS['TL_DCA'][$strTable]['fields'])
            {
                foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k=>$v)
                {
                    if ($v['exclude'])
                    {
                        if ($this->User->hasAccess($strTable.'::'.$k, 'alexf'))
                        {
                            if ($strTable == 'tl_user_group')
                            {
                                $GLOBALS['TL_DCA'][$strTable]['fields'][$k]['orig_exclude'] = $GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'];
                            }

                            $GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'] = false;
                        }
                    }
                }
            }

            // Fabricate a new data container object
            if ($GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'] == '')
            {
                $this->log('Missing data container for table "' . $strTable . '"', __METHOD__, TL_ERROR);
                trigger_error('Could not create a data container object', E_USER_ERROR);
            }

            $dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
            $dc = new $dataContainer($strTable, $arrModule);
        }

        // AJAX request
        if ($_POST && \Environment::get('isAjaxRequest'))
        {
            $this->objAjax->executePostActions($dc);
        }

        // Trigger the module callback
        elseif (class_exists($arrModule['callback']))
        {
            $objCallback = new $arrModule['callback']($dc);
            $this->Template->main .= $objCallback->generate();
        }

        // Custom action (if key is not defined in config.php the default action will be called)
        elseif (\Input::get('key') && isset($arrModule[\Input::get('key')]))
        {
            $objCallback = new $arrModule[\Input::get('key')][0]();
            $this->Template->main .= $objCallback->$arrModule[\Input::get('key')][1]($dc);

            // Add the name of the parent element
            if (isset($_GET['table']) && in_array(\Input::get('table'), $arrModule['tables']) && \Input::get('table') != $arrModule['tables'][0])
            {
                if ($GLOBALS['TL_DCA'][$strTable]['config']['ptable'] != '')
                {
                    $objRow = $this->Database->prepare("SELECT * FROM " . $GLOBALS['TL_DCA'][$strTable]['config']['ptable'] . " WHERE id=?")
                        ->limit(1)
                        ->execute(CURRENT_ID);

                    if ($objRow->title != '')
                    {
                        $this->Template->headline .= ' » ' . $objRow->title;
                    }
                    elseif ($objRow->name != '')
                    {
                        $this->Template->headline .= ' » ' . $objRow->name;
                    }
                }
            }

            // Add the name of the submodule
            $this->Template->headline .= ' » ' . sprintf($GLOBALS['TL_LANG'][$strTable][\Input::get('key')][1], \Input::get('id'));
        }

        // Default action
        elseif (is_object($dc))
        {
            $act = \Input::get('act');

            if ($act == '' || $act == 'paste' || $act == 'select')
            {
                $act = ($dc instanceof \listable) ? 'showAll' : 'edit';
            }

            switch ($act)
            {
                case 'delete':
                case 'show':
                case 'showAll':
                case 'undo':
                    if (!$dc instanceof \listable)
                    {
                        $this->log('Data container ' . $strTable . ' is not listable', __METHOD__, TL_ERROR);
                        trigger_error('The current data container is not listable', E_USER_ERROR);
                    }
                    break;

                case 'create':
                case 'cut':
                case 'cutAll':
                case 'copy':
                case 'copyAll':
                case 'move':
                case 'edit':
                    if (!$dc instanceof \editable)
                    {
                        $this->log('Data container ' . $strTable . ' is not editable', __METHOD__, TL_ERROR);
                        trigger_error('The current data container is not editable', E_USER_ERROR);
                    }
                    break;
            }

            // Add the name of the parent element
            if ($strTable && in_array($strTable, $arrModule['tables']) && $strTable != $arrModule['tables'][0])
            {
                if ($GLOBALS['TL_DCA'][$strTable]['config']['ptable'] != '')
                {
                    $objRow = $this->Database->prepare("SELECT * FROM " . $GLOBALS['TL_DCA'][$strTable]['config']['ptable'] . " WHERE id=?")
                        ->limit(1)
                        ->execute(CURRENT_ID);

                    if ($objRow->title != '')
                    {
                        $this->Template->headline .= ' » ' . $objRow->title;
                    }
                    elseif ($objRow->name != '')
                    {
                        $this->Template->headline .= ' » ' . $objRow->name;
                    }
                }
            }

            // Add the name of the submodule
            if ($strTable && isset($GLOBALS['TL_LANG']['MOD'][$strTable]))
            {
                $this->Template->headline .= ' » ' . $GLOBALS['TL_LANG']['MOD'][$strTable];
            }

            // Add the current action
            if (\Input::get('act') == 'editAll')
            {
                $this->Template->headline .= ' » ' . $GLOBALS['TL_LANG']['MSC']['all'][0];
            }
            elseif (\Input::get('act') == 'overrideAll')
            {
                $this->Template->headline .= ' » ' . $GLOBALS['TL_LANG']['MSC']['all_override'][0];
            }
            elseif (is_array($GLOBALS['TL_LANG'][$strTable][$act]) && \Input::get('id'))
            {
                if (\Input::get('do') == 'files')
                {
                    $this->Template->headline .= ' » ' . \Input::get('id');
                }
                else
                {
                    $this->Template->headline .= ' » ' . sprintf($GLOBALS['TL_LANG'][$strTable][$act][1], \Input::get('id'));
                }
            }

            return $dc->$act();
        }

        return null;
    }

}
