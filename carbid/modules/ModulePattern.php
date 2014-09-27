<?php

/**
 * Pattern for Contao Open Source CMS
 *
 * Copyright (C) 2014 Alexander Stulnikov
 *
 * @package    Pattern
 * @link       https://github.com/alarstyle/contao-pattern
 * @license    http://opensource.org/licenses/MIT
 */


namespace Carbid;


/**
 * Class ModulePattern
 */
class ModulePattern extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_pattern';

    protected $data;


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        $this->data = Pattern::parseData($this->pattern_data, $this->type);

        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_pattern');

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
