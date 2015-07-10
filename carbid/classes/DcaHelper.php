<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

namespace Carbid;

use \Contao\Input;
use \Contao\Image;
use \Contao\Versions;

class DcaHelper extends \Backend
{

    /**
     * Table name
     * @var string
     */
    public $tableName = '';


    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (empty($this->tableName))
            return '';

        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess($this->tableName.'::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Disable/enable a user group
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');
        //$this->checkPermission();

        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess($this->tableName . '::published', 'alexf'))
        {
            $this->log('Not enough permissions to publish/unpublish event ID "'.$intId.'"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions($this->tableName, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][$this->tableName]['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][$this->tableName]['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE ". $this->tableName ." SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "'. $this->tableName .'.id='.$intId.'" has been created'.$this->getParentEntries($this->tableName, $intId), __METHOD__, TL_GENERAL);
    }


    /**
     * Return the "copy" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (empty($this->tableName))
            return '';

        if ($row['pid'] > 0)
        {
            return '<a href="'.preg_replace('/&(amp;)?id=[^& ]*/i', '', ampersand(\Environment::get('request'))).'&amp;act=paste&amp;mode=copy&amp;table='. $this->tableName .'&amp;id='.$row['id'].'&amp;pid='.\Input::get('id').'" title="'.specialchars($title).'"'.$attributes.' onclick="Backend.getScrollOffset();">'.\Image::getHtml($icon, $label).'</a> ';
        }

        return '<a href="'.\Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }
}
