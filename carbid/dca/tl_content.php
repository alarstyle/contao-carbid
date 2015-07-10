<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]     = array('Carbid\Pattern', 'getVariables');
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][]   = array('Carbid\Pattern', 'saveVariables');
$GLOBALS['TL_DCA']['tl_content']['fields']['pattern_data']          = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_content']['pattern_data'],
	'exclude'   => true,
	'inputType' => 'hidden',
    'eval'      => array('tl_class' => 'hidden'),
	'sql'       => "mediumblob NULL",
	'save_callback' => array(
		array('Carbid\Pattern', 'saveData'),
	),
);
