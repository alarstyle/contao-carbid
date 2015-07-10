<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][]      = array('Carbid\Pattern', 'getVariables');
$GLOBALS['TL_DCA']['tl_module']['config']['onsubmit_callback'][]    = array('Carbid\Pattern', 'saveVariables');
$GLOBALS['TL_DCA']['tl_module']['fields']['pattern_data']           = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['pattern_data'],
	'exclude'   => true,
    'inputType' => 'hidden',
    'eval'      => array('tl_class' => 'hidden'),
	'sql'       => "mediumblob NULL",
	'save_callback' => array(
        array('Carbid\Pattern', 'saveData'),
	),
);
