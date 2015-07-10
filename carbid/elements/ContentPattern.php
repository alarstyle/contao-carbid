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


/**
 * Class ContentPattern
 */
class ContentPattern extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_pattern';

    protected $data;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
        $this->data = Pattern::parseData($this->pattern_data, $this->type);

		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_pattern');

            //$templateTitle = &$GLOBALS['TL_LANG']['CTE'][$this->type];

			//$objTemplate->wildcard = '### ' . $templateTitle . ' ###';

            $objTemplate->variables = $this->data;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
        $objPattern = new PatternTemplate($this->type);

        $objPattern->variables = $this->data;

        $this->Template->pattern = $objPattern->parse();
	}
}
