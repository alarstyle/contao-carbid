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
 * Class PolyTree
 *
 * Provide methods to handle input field "picker".
 */
class Picker extends \Contao\Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';

	/**
	 * Order ID
	 * @var string
	 */
	protected $strOrderId;

	/**
	 * Order name
	 * @var string
	 */
	protected $strOrderName;


	/**
	 * Load the database object
	 * @param array
	 */
	public function __construct($arrAttributes=null)
	{
		$this->import('Database');
		parent::__construct($arrAttributes);

		// Prepare the order field
		if ($this->orderField != '')
		{
			$this->strOrderId = $this->orderField . str_replace($this->strField, '', $this->strId);
			$this->strOrderName = $this->orderField . str_replace($this->strField, '', $this->strName);

			// Retrieve the order value
			$objRow = $this->Database->prepare("SELECT {$this->orderField} FROM {$this->strTable} WHERE id=?")
						   ->limit(1)
						   ->execute($this->activeRecord->id);

			$tmp = deserialize($objRow->{$this->orderField});
			$this->{$this->orderField} = (!empty($tmp) && is_array($tmp)) ? array_filter($tmp) : array();
		}
	}


	/**
	 * Return an array if the "multiple" attribute is set
	 * @param mixed
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		// Store the order value
		if ($this->orderField != '')
		{
			$arrNew = explode(',', \Input::post($this->strOrderName));

			// Only proceed if the value has changed
			if ($arrNew !== $this->{$this->orderField})
			{
				$this->Database->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->orderField}=? WHERE id=?")
							   ->execute(time(), serialize($arrNew), $this->activeRecord->id);

				$this->objDca->createNewVersion = true; // see #6285
			}
		}

		// Return the value as usual
		if ($varInput == '')
		{
			if ($this->mandatory)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			}

			return '';
		}
		elseif (strpos($varInput, ',') === false)
		{
			return $this->multiple ? array(intval($varInput)) : intval($varInput);
		}
		else
		{
			$arrValue = array_map('intval', array_filter(explode(',', $varInput)));
			return $this->multiple ? $arrValue : $arrValue[0];
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$arrSet = array();
		$arrValues = array();
		$blnHasOrder = ($this->orderField != '' && is_array($this->{$this->orderField}));

		if (!empty($this->varValue)) // Can be an array
		{
            $model = $this->treeModel;

			$objItem = $model::findMultipleByIds((array)$this->varValue);

			if ($objItem !== null)
			{
				while ($objItem->next())
				{
					$arrSet[] = $objItem->id;
                    // TODO: output
					$arrValues[$objItem->id] = $objItem->name;
                    if ($this->itemIcon) {
                        $arrValues[$objItem->id] = \Image::getHtml($this->itemIcon) . ' ' . $arrValues[$objItem->id];
                    }
				}
			}

			// Apply a custom sort order
			if ($blnHasOrder)
			{
				$arrNew = array();

				foreach ($this->{$this->orderField} as $i)
				{
					if (isset($arrValues[$i]))
					{
						$arrNew[$i] = $arrValues[$i];
						unset($arrValues[$i]);
					}
				}

				if (!empty($arrValues))
				{
					foreach ($arrValues as $k=>$v)
					{
						$arrNew[$k] = $v;
					}
				}

				$arrValues = $arrNew;
				unset($arrNew);
			}
		}

		// Load the fonts for the drag hint (see #4838)
		\Config::set('loadGoogleFonts', true);

		$return = '<input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.implode(',', $arrSet).'">' . ($blnHasOrder ? '
  <input type="hidden" name="'.$this->strOrderName.'" id="ctrl_'.$this->strOrderId.'" value="'.$this->{$this->orderField}.'">' : '') . '
  <div class="selector_container">' . (($blnHasOrder && count($arrValues) > 1) ? '
    <p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>' : '') . '
    <ul id="sort_'.$this->strId.'" class="'.($blnHasOrder ? 'sortable' : '').'">';

		foreach ($arrValues as $k=>$v)
		{
			$return .= '<li data-id="'.$k.'">'.$v.'</li>';
		}

		$return .= '</ul>
    <p><a href="contao/picker.php?do='.\Input::get('do').'&amp;table='.$this->strTable.'&amp;field='.$this->strField.'&amp;act=show&amp;id='.$this->activeRecord->id.'&amp;value='.implode(',', $arrSet).'&amp;rt='.REQUEST_TOKEN.'" class="tl_submit" onclick="Backend.getScrollOffset();Carbid.openModalSelector({\'width\':768,\'title\':\''.specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']).'\',\'url\':this.href,\'id\':\''.$this->strId.'\'});return false">'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</a></p>' . ($blnHasOrder ? '
    <script>Backend.makeMultiSrcSortable("sort_'.$this->strId.'", "ctrl_'.$this->strOrderId.'")</script>' : '') . '
  </div>';

		if (!\Environment::get('isAjaxRequest'))
		{
			$return = '<div>' . $return . '</div>';
		}

		return $return;
	}
}
