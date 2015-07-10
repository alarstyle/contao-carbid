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

class DatabaseUpdater extends \Database\Installer
{

    /**
     * Automatically add and update columns and keys
     * @param    array
     */
    public function autoUpdateTables($arrTables)
    {
        $arrCommands = $this->compileCommands();

        foreach ($arrTables as $strTable) {

            if (!empty($arrCommands['ALTER_DROP']) && is_array($arrCommands['ALTER_DROP'])) {
                foreach ($arrCommands['ALTER_DROP'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '` DROP INDEX') === 0) {
                        \Database::getInstance()->query($strCommand);
                    }
                }
            }

            if (!empty($arrCommands['ALTER_CHANGE']) && is_array($arrCommands['ALTER_CHANGE'])) {
                foreach ($arrCommands['ALTER_CHANGE'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '`') === 0) {
                        \Database::getInstance()->query($strCommand);
                    }
                }
            }

            if (!empty($arrCommands['ALTER_ADD']) && is_array($arrCommands['ALTER_ADD'])) {
                foreach ($arrCommands['ALTER_ADD'] as $strCommand) {
                    if (strpos($strCommand, 'ALTER TABLE `' . $strTable . '`') === 0) {
                        \Database::getInstance()->query($strCommand);
                    }
                }
            }
        }
    }
}
