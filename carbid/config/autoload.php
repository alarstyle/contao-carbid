<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
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
	'Carbid\BackendLanguages'       => 'system/modules/carbid/library/Carbid/BackendLanguages.php',
	'Carbid\DatabaseUpdater'        => 'system/modules/carbid/library/Carbid/DatabaseUpdater.php',
	'Carbid\DynamicDCA'             => 'system/modules/carbid/library/Carbid/DynamicDCA.php',
    'Carbid\InsertTags'             => 'system/modules/carbid/library/Carbid/InsertTags.php',
	'Carbid\Pattern'                => 'system/modules/carbid/library/Carbid/Pattern.php',
    'Carbid\PatternTemplate'        => 'system/modules/carbid/library/Carbid/PatternTemplate.php',
    'Carbid\PickerHooks'            => 'system/modules/carbid/library/Carbid/PickerHooks.php',

    // Elements
    'Carbid\ContentPattern'         => 'system/modules/carbid/elements/ContentPattern.php',

    // Modules
    'Carbid\ModulePattern'          => 'system/modules/carbid/modules/ModulePattern.php',
    'Carbid\ModuleShortcut'         => 'system/modules/carbid/modules/ModuleShortcut.php',

	// Widgets
	'Carbid\Widget\CheckBoxTree'    => 'system/modules/carbid/widgets/CheckBoxTree.php',
	'Carbid\Widget\Hidden'          => 'system/modules/carbid/widgets/Hidden.php',
	'Carbid\Widget\Picker'          => 'system/modules/carbid/widgets/Picker.php',
));


/**
 * Register templates
 */
TemplateLoader::addFiles(array
(
    'be_shop_config'        => 'system/modules/carbid/templates/backend',
    'be_pattern'            => 'system/modules/carbid/templates/backend',
    'be_widget_hidden'      => 'system/modules/carbid/templates/backend',
    'ce_pattern'            => 'system/modules/carbid/templates/elements',
    'mod_pattern'           => 'system/modules/carbid/templates/modules',
));