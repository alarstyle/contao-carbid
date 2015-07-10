<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014-2015 Alexander Stulnikov
 *
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

namespace Carbid\Widget;


/**
 * Class CheckBoxTree
 *
 * Options:
 * tableName - table to load tree hierarchy
 */
class CheckBoxTree extends \CheckBox
{

    /**
     * Counting lines to add proper class 'even' or 'odd'
     * @var int
     */
    public $intLinesCount = 0;


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $items = \Carbid\Helper::getTreeFromTable($this->arrConfiguration['tableName']);

        // return default if no items or not systemColumn
        if (empty ($this->arrOptions) || empty($items) || !$this->arrConfiguration['systemColumn']) {
            return parent::generate();
        }

        $list = $this->generateList($items);

        $arrOptions = array();

        if (!$this->multiple && count($this->arrOptions) > 1)
        {
            $this->arrOptions = array($this->arrOptions[0]);
        }

        // The "required" attribute only makes sense for single checkboxes
        if (!$this->multiple && $this->mandatory)
        {
            $this->arrAttributes['required'] = 'required';
        }

        $state = $this->Session->get('checkbox_groups');

        // Toggle the checkbox group
        if (\Input::get('cbc'))
        {
            $state[\Input::get('cbc')] = (isset($state[\Input::get('cbc')]) && $state[\Input::get('cbc')] == 1) ? 0 : 1;
            $this->Session->set('checkbox_groups', $state);

            $this->redirect(preg_replace('/(&(amp;)?|\?)cbc=[^& ]*/i', '', \Environment::get('request')));
        }

        $blnFirst = false;
        $blnCheckAll = false;

        foreach ($this->arrOptions as $i=>$arrOption)
        {
            // Single dimension array
            if (is_numeric($i))
            {
                $arrOptions[] = $this->generateCheckbox($arrOption, $i);
                continue;
            }
        }

        if ($this->multiple)
        {
            return sprintf('<fieldset id="ctrl_%s" class="tl_checkbox_container%s tl_checkbox_tree"><legend>%s%s%s%s</legend><input type="hidden" name="%s" value="">%s%s</fieldset>%s',
                $this->strId,
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                ($this->mandatory ? '<span class="invisible">'.$GLOBALS['TL_LANG']['MSC']['mandatory'].'</span> ' : ''),
                $this->strLabel,
                ($this->mandatory ? '<span class="mandatory">*</span>' : ''),
                $this->xlabel,
                $this->strName,
                ($blnCheckAll ? '<input type="checkbox" id="check_all_' . $this->strId . '" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,\'ctrl_' . $this->strId . '\')' . ($this->onclick ? ';' . $this->onclick : '') . '"> <label for="check_all_' . $this->strId . '" style="color:#a6a6a6"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>' : ''),
                str_replace('<br></fieldset><br>', '</fieldset>', $list),
                $this->wizard);
        }
        else
        {
            return sprintf('<div id="ctrl_%s" class="tl_checkbox_single_container%s tl_checkbox_tree"><input type="hidden" name="%s" value="">%s</div>%s',
                $this->strId,
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                $this->strName,
                str_replace('<br></div><br>', '</div>', $list),
                $this->wizard);
        }
    }


    /**
     * Generate list of checkboxes
     */
    public function generateList($items) {
        $strList = '';
        foreach($items as $item) {
            $this->intLinesCount += 1;
            $strClass = $this->intLinesCount % 2 == 0 ? 'even' : 'odd';
            $innerList = !empty($item['children']) ? $this->generateList($item['children']) : '';
            foreach ($this->arrOptions as $i=>$arrOption)
            {
                if ($arrOption['value'] != $item['id'])
                    continue;
                $checkbox = $this->generateCheckbox($arrOption, $i);
                break;
            }
            $strList .= '<li><div class="'. $strClass .'">'. $checkbox .'</div>'. $innerList .'</li>';
        }
        return '<ul>'. $strList .'</ul>';
    }

}
