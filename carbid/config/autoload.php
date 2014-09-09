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


/**
 * Register namespaces
 */
ClassLoader::addNamespaces(array('Carbid'));


/**
 * Register classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'Carbid\Carbid'                 => 'system/modules/carbid/classes/Carbid.php',
	'Carbid\DcaHelper'              => 'system/modules/carbid/classes/DcaHelper.php',
	'Carbid\Helper'                 => 'system/modules/carbid/classes/Helper.php',

	// Widgets
	'Carbid\Widget\CheckBoxTree'    => 'system/modules/carbid/widgets/CheckBoxTree.php',
));
