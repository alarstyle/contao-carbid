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

class PickerHooks extends \Contao\Backend
{

    function executePostActions($strAction, $dc)
    {
        switch($strAction)
        {
            case 'reloadPicker':
                $intId = \Input::get('id');
                $strField = $dc->field = \Input::post('name');

                // Handle the keys in "edit multiple" mode
                if (\Input::get('act') == 'editAll')
                {
                    $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                // The field does not exist
                if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]))
                {
                    $this->log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }

                $objRow = null;
                $varValue = null;

                // Load the value
                if ($GLOBALS['TL_DCA'][$dc->table]['config']['dataContainer'] == 'File')
                {
                    $varValue = \Config::get($strField);
                }
                elseif ($intId > 0 && $this->Database->tableExists($dc->table))
                {
                    $objRow = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
                        ->execute($intId);

                    // The record does not exist
                    if ($objRow->numRows < 1)
                    {
                        $this->log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
                        header('HTTP/1.1 400 Bad Request');
                        die('Bad Request');
                    }

                    $varValue = $objRow->$strField;
                    $dc->activeRecord = $objRow;
                }

                // Call the load_callback
                if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback']))
                {
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'] as $callback)
                    {
                        if (is_array($callback))
                        {
                            $this->import($callback[0]);
                            $varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
                        }
                        elseif (is_callable($callback))
                        {
                            $varValue = $callback($varValue, $dc);
                        }
                    }
                }

                // Set the new value
                $varValue = \Input::post('value', true);

                $strKey = 'picker';


                // Convert the selected values
                if ($varValue != '')
                {
                    $varValue = trimsplit("\t", $varValue);
                    $varValue = serialize($varValue);
                }

                $strClass = $GLOBALS['BE_FFL'][$strKey];
                $objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField], $dc->field, $varValue, $strField, $dc->table, $dc));

                echo $objWidget->generate();
                exit; break;
        }
    }

}