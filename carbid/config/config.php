<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */


require_once TL_ROOT . '/system/modules/carbid/helper/functions.php';


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['checkboxTree']          = 'Carbid\Widget\CheckBoxTree';
$GLOBALS['BE_FFL']['hidden']                = 'Carbid\Widget\Hidden';
$GLOBALS['BE_FFL']['picker']                = 'Carbid\Widget\Picker';


/**
 * Backend only
 */
if (TL_MODE == 'BE')
{
    /**
     * Hooks
     */
    $GLOBALS['TL_HOOKS']['initializeSystem'][]      = array('Carbid\Carbid', 'initializeSystem');
    $GLOBALS['TL_HOOKS']['getUserNavigation'][]     = array('Carbid\Carbid', 'getUserNavigation');
    $GLOBALS['TL_HOOKS']['parseBackendTemplate'][]  = array('Carbid\Carbid', 'parseBackendTemplate');
    $GLOBALS['TL_HOOKS']['executePostActions'][]    = array('Carbid\PickerHooks', 'executePostActions');

    // Backend languages
    $GLOBALS['TL_HOOKS']['getLanguages'][]          = array('Carbid\BackendLanguages', 'getLanguagesHook');
}


// Pattern hooks
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Carbid\Pattern', 'initializeSystemHook');
$GLOBALS['TL_HOOKS']['loadLanguageFile'][] = array('Carbid\Pattern', 'loadLanguageFileHook');
if (is_array($GLOBALS['TL_HOOKS']['loadDataContainer']))
{
    array_insert($GLOBALS['TL_HOOKS']['loadDataContainer'], 0, array('Carbid\Pattern', 'loadDataContainerHook'));
}
else
{
    $GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Carbid\Pattern', 'loadDataContainerHook');
}


// Pattern categories
$GLOBALS['TL_CTE']['patterns'] = array();
$GLOBALS['FE_MOD']['patterns'] = array();


// Insert tags
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Carbid\InsertTags', 'replaceInsertTagsHook');
$GLOBALS['TL_HOOKS']['insertTagFlags'][]    = array('Carbid\InsertTags', 'insertTagFlagsHook');


