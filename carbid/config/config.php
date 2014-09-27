<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014 Alexander Stulnikov
 *
 * @package    Carbid
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */


require_once TL_ROOT . '/system/modules/carbid/helper/functions.php';


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['checkboxTree']          = 'Carbid\Widget\CheckBoxTree';
$GLOBALS['BE_FFL']['hidden']                = 'Carbid\Widget\Hidden';


/**
 * Backend only
 */
if (TL_MODE == 'BE')
{
    /**
     * Add CSS and JS
     */
    $GLOBALS['TL_JAVASCRIPT'][]     = 'system/modules/carbid/assets/js/carbid.js';
    $GLOBALS['TL_CSS'][]            = 'system/modules/carbid/assets/css/carbid.css';

    /**
     * Hooks
     */
    $GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Carbid\Carbid', 'initializeSystem');
    $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('Carbid\Carbid', 'parseBackendTemplate');
}


// Pattern hooks
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Carbid\Pattern', 'initializeSystem');
$GLOBALS['TL_HOOKS']['loadLanguageFile'][] = array('Carbid\Pattern', 'loadLanguageFileHook');


// Pattern categories
$GLOBALS['TL_CTE']['patterns'] = array();
$GLOBALS['FE_MOD']['patterns'] = array();


