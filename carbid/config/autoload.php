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
	'Carbid\DynamicDCA'             => 'system/modules/carbid/library/Carbid/DynamicDCA.php',
	'Carbid\Pattern'                => 'system/modules/carbid/library/Carbid/Pattern.php',
	'Carbid\PatternTemplate'        => 'system/modules/carbid/library/Carbid/PatternTemplate.php',

    // Elements
    'Carbid\ContentPattern'         => 'system/modules/carbid/elements/ContentPattern.php',

    // Modules
    'Carbid\ModulePattern'          => 'system/modules/carbid/modules/ModulePattern.php',

	// Widgets
	'Carbid\Widget\CheckBoxTree'    => 'system/modules/carbid/widgets/CheckBoxTree.php',
	'Carbid\Widget\Hidden'          => 'system/modules/carbid/widgets/Hidden.php',
));


/**
 * Register templates
 */
TemplateLoader::addFiles(array
(
    'be_pattern'            => 'system/modules/carbid/templates/backend',
    'be_widget_hidden'      => 'system/modules/carbid/templates/backend',
    'ce_pattern'            => 'system/modules/carbid/templates/elements',
    'mod_pattern'           => 'system/modules/carbid/templates/modules',
));