<?php

namespace Webster\Shop\Controllers;

use Webster\Shop\Handler\Dispatcher;
use Webster\Shop\Persistence\AbstractProcessor;

/**
 * <REPLACE WITH FILE DESCRIPTION>
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    $package
 * @subpackage $subPackage
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */
class AbstractController
{
    /**
     * @var  $processor AbstractProcessor
     */
    protected $processor;

    /**
     * @var  $settings array
     */
    protected $settings;

    /**
     * Constructor which takes several settings.
     *
     * @param $settings mixed
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function setProcessor(AbstractProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}