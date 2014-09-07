<?php

namespace Webster\ShopBackend\Persistence;

use Noodlehaus\Config;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\MongoDB\Connection;
use Webster\ShopBackend\Persistence\AbstractProcessor;

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
class ProcessorFactory
{
    /**
     * @var array $_config Holds the persistence configuration.
     */
    private $_config;

    /**
     * @var array $_processors Holds the processors.
     */
    private $_processors;

    /**
     * @var DocumentManager $_dm Holds the document manager.
     */
    private $_dm;

    /**
     * Constructs the processor factory.
     *
     * @param array $config
     */
    public function __construct(Config $config)
    {
        $this->_config = $config->get('persistence');
        $this->_processors = $config->get('processors');
        $this->_setupDoctrine();
    }

    /**
     * Initializes and returns a processor specified by the given key.
     *
     * @param string $key Specifies which processor to initialize
     * @return AbstractProcessor
     * @throws \Exception
     */
    public function get($key)
    {
        // check if the requested key is available
        if(!array_key_exists($key, $this->_processors)){
            throw new \Exception('The requested processor for key ' . $key . ' does not exist');
        }

        // get the processor class name
        $ns = $this->_config['namespace'];
        $processorClass = $ns . '\\' . $this->_processors[$key];

        // check if processor class exists
        if(!class_exists($processorClass)){
            throw new \Exception('The requested class ' . $processorClass . ' does not exist');
        }

        // initialize the processor
        $processor = new $processorClass($this->_dm);

        return $processor;
    }

    /**
     * Initializes and sets up doctrine for mongodb.
     */
    protected function _setUpDoctrine()
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        AnnotationDriver::registerAnnotationClasses();

        $config = new Configuration();
        $config->setProxyDir($this->_config['proxy_dir']);
        $config->setProxyNamespace($this->_config['proxy_namespace']);
        $config->setHydratorDir($this->_config['hydrator_dir']);
        $config->setHydratorNamespace($this->_config['hydrator_namespace']);
        $config->setMetadataDriverImpl(AnnotationDriver::create($this->_config['entity_path']));
        $config->setDefaultDB($this->_config['database']);

        $this->_dm = DocumentManager::create(new Connection(), $config);
    }
}