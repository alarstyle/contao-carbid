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


namespace Carbid;

class Carbid
{

    public function initializeSystem() {
        /*$session = \Session::getInstance()->getData();
        var_dump($session['backend_modules']);
        var_dump('----');
        $session['backend_modules'] = array();
        \Session::getInstance()->set('backend_modules', array());
        $session = \Session::getInstance()->getData();
        var_dump($session['backend_modules']);*/
    }

    public function parseBackendTemplate($strContent, $strTemplate)
    {
        /*$user = \BackendUser::getInstance();*/

        // Add classes
        $strClasses = ' template-' . $strTemplate;
        if (\Input::get('do')) {
            $strClasses .= ' do-' . \Input::get('do');
        }
        if (\Input::get('table')) {
            $strClasses .= ' table-' . \Input::get('table');
        }
        if (\Input::get('act')) {
            $strClasses .= ' act-' . \Input::get('act');
        }
        if (\Config::get('debugMode')) {
            $strClasses .= ' debug-enabled';
        }
        $strContent =  preg_replace('/(<body) ?(([^>]*)class="([^"]*)")?/', '$1 $3 class="$4 ' . $strClasses . '" ', $strContent);

        // Replace filter button
        $strContent = preg_replace('/<input(.*?)type="image"(.*?)>/', '<button $1 $2 $3></button>', $strContent);

        // Remove collapsible
        //$strContent = str_replace(array('collapsible_area', 'collapsible'), '', $strContent);

        return $strContent;
    }

}