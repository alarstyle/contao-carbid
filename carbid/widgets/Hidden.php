<?php

namespace Carbid\Widget;

/**
 * Hidden widget
 *
 * @author Martin Auswöger <martin@madeyourday.net>
 */
class Hidden extends \Widget
{
	/**
	 * @var boolean Submit user input
	 */
	protected $blnSubmitInput = true;

	/**
	 * @var string Template
	 */
	protected $strTemplate = 'be_widget_hidden';

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		return '';
	}
}
