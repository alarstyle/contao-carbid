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

class BackendLanguages
{

    public static $skipFiltering = false;

    public function getLanguagesHook(&$return, $languages, $langsNative, $blnInstalledOnly)
    {
        // Do nothing if it is not login page or user management page
        if (TL_SCRIPT !== 'contao/index.php' && \Input::get('do') !== 'user') {
            return;
        }

        if (static::$skipFiltering || (!static::$skipFiltering && !\Config::get('beShowOnlyEnabledLanguages')))
        {
            return;
        }

        $enabledLanguages = deserialize(\Config::get('beEnabledLanguages'));
        $arrNewReturn = array();

        foreach ($enabledLanguages as $languageKey)
        {
            if ($return[$languageKey])
            {
                $arrNewReturn[$languageKey] = $return[$languageKey];
            }
        }

        $return = $arrNewReturn;
    }

    public function getAllBackendLanguages()
    {
        static::$skipFiltering = true;
        $arrLanguages = \System::getLanguages(true);
        static::$skipFiltering = false;
        return $arrLanguages;
    }

}