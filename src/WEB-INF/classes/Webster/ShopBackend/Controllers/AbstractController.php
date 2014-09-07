<?php

namespace Webster\ShopBackend\Controllers;

use Webster\ShopBackend\Persistence\ProcessorFactory;

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
abstract class AbstractController
{
    /**
     * @var ProcessorFactory $_processorFactory Holds the process factory.
     */
    private $_processorFactory;

    /**
     * Constructs a controller instance.
     *
     * @param ProcessorFactory $processorFactory
     */
    public function __construct(ProcessorFactory $processorFactory)
    {
        $this->_processorFactory = $processorFactory;
        $this->_init();
    }

    /**
     * Initializes the controller instance.
     *
     * @return mixed
     */
    abstract protected function _init();

    /**
     * Returns the processor factory.
     *
     * @return ProcessorFactory
     */
    public function getProcessorFactory()
    {
        return $this->_processorFactory;
    }
}