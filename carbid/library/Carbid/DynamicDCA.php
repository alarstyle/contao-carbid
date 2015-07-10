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
 * Class DynamicDCA
 */
class DynamicDCA
{

    public static function getLabelTranslation($labelData, $language)
    {
        if (!isset($labelData)) {
            return null;
        }

        if (!is_array($labelData))
        {
            return array($labelData, '');
        }

        if (!count(array_filter(array_keys($labelData), 'is_string'))) {
            return $labelData;
        }

        if (isset($labelData[$language])) {
            return is_array($labelData[$language]) ? $labelData[$language] : array($labelData[$language], '');
        }

        if (isset($labelData['en'])) {
            return is_array($labelData['en']) ? $labelData['en'] : array($labelData['en'], '');
        }

        $arr = array_values($labelData);
        return is_array($arr[0]) ? $arr[0] : array($arr[0], '');
    }


    public static function getVariablesValues($data, $arrFields)
    {

    }


    public static function getVariablesTypes($arrFields)
    {

    }


}