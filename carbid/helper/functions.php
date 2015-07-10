<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */


function url_by_id($pageId, $strParams=null, $strForceLang=null) {
    return \Frontend::generateFrontendUrl(\PageModel::findByPk($pageId)->row(), $strParams, $strForceLang);
}

function url_for_lang(array $arrObj, $strLang) {
    if (!empty($arrObj[$strLang])) {
        return url_by_id($arrObj[$strLang]);
    }
    return '';
}
