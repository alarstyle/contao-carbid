<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'beShowOnlyEnabledLanguages';

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{beLanguages_legend},showLoginLanguage,beShowOnlyEnabledLanguages';

$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['beShowOnlyEnabledLanguages'] = 'beEnabledLanguages';

$GLOBALS['TL_DCA']['tl_settings']['fields']['showLoginLanguage'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_settings']['showLoginLanguage'],
    'exclude'       => true,
    'inputType'     => 'checkbox',
    'eval'          => array('tl_class'=>'w50 float-right'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['beShowOnlyEnabledLanguages'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_settings']['beShowOnlyEnabledLanguages'],
    'exclude'       => true,
    'inputType'     => 'checkbox',
    'eval'          => array('submitOnChange'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['beEnabledLanguages'] = array
(
    'label'             => &$GLOBALS['TL_LANG']['tl_settings']['beEnabledLanguages'],
    'exclude'           => true,
    'inputType'         => 'select',
    'options_callback'  => array('Carbid\BackendLanguages', 'getAllBackendLanguages'),
    'eval'              => array('multiple'=>true, 'chosen'=>true, 'mandatory'=>true, 'tl_class'=>'long'),
);

