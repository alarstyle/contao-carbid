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

class InsertTags extends \Controller
{

    public function replaceInsertTagsHook($strTag, $blnCache, $strBuffer, $flags)
    {
        return false;

        /*$arrParts = explode('::', $strTag);

        switch ($arrParts[0])
        {
            case 'tag':
                return 'some text';
                break;

            default:
                return false;
        }*/
    }

    public function insertTagFlagsHook($strFlag, $strTag, $strBuffer)
    {
        global $objPage;

        switch ($strFlag)
        {
            case 'check_current':

                $arrParts = explode('::', $strTag);

                if ($arrParts[0] !== 'link' && $arrParts[0] !== 'link_open')
                {
                    return $strBuffer;
                }
                if ($objPage->alias !== $arrParts[1] && $objPage->id !== $arrParts[1])
                {
                    return $strBuffer;
                }

                return str_replace('<a ', '<a class="current"', $strBuffer);

                break;

            default:
                return false;
        }
    }

}