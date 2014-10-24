<?php

namespace Webster\ShopBackend\Handler;

use Ratchet\ConnectionInterface;
use Webster\ShopBackend\Messages\ResponseMessage;
use Noodlehaus\Config;
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
class Dispatcher
{
    /**
     * @var $_config Config Holds the app configuration.
     */
    private $_config;

    /**
     * @var  $_processorFactory ProcessorFactory Holds the processor factory.
     */
    private $_processorFactory;

    /**
     * Constructor for dispatcher.
     *
     * @param Config $config
     */
    public function __construct(Config $config, ProcessorFactory $processorFactory)
    {
        $this->_config = $config;
        $this->_processorFactory = $processorFactory;
    }

    /**
     * Dispatches an incoming message according to requested controller and action.
     *
     * @param $message string
     * @param ConnectionInterface $connection
     * @throws \Exception
     */
    public function dispatchMessage($message, ConnectionInterface $connection)
    {
        // check if message contains characters
        if(!$message = trim($message)){
            return false;
        }

        // get configuration
        $config = $this->getConfig();

        // check if message is valid
        if( !($message = json_decode($message)) ){
            throw new \Exception('The message could not be deserialized correctly.');
        }
        if( (!$message->type) || (!$message->action) ){
            throw new \Exception('The message is not correct.');
        }

        // extract controller and action from message
        $classInfo = $this->getClassInfo($message->type);
        $controllerClass = $config->get('namespace') . '\\Controllers\\' . $classInfo['module'] . '\\' . $classInfo['type'] . 'Controller';
        $action = $message->action . 'Action';

        // check class and action
        if(!class_exists($controllerClass)){
            throw new \Exception('Controller class "' . $controllerClass . '" could not be found.');
        }
        if(!method_exists($controllerClass, $action)){
            throw new \Exception('Controller class "' . $controllerClass . '" has no action "' . $action . '.');
        }

        // initialize the controller instance
        $controller = new $controllerClass($this->getProcessorFactory());

        // call the requested action, handing over the message content and an initialized response message
        $controller->$action($message->content, new ResponseMessage($connection));
    }

    /**
     * Returns the configuration.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Returns the processor factory.
     *
     * @return ProcessorFactory
     */
    public function getProcessorFactory()
    {
        return $this->_processorFactory;
    }

    /**
     * Extracts class information out of a given class id like module and type.
     *
     * @param string $classId
     * @return array
     */
    public function getClassInfo($classId)
    {
        $result = array();

        // if class id is valid extract module and type
        if(strpos($classId, '/')){
            $classArr = explode('/', trim($classId));
            $module = $classArr[0];
            $type = !empty($classArr[1]) ? $classArr[1] : null;

            $module = $this->ucwords($module, '\\');
            $type = $this->ucwords($type, '\\');

            $result['module'] = $module ? $module : '';
            $result['type'] = $type ? $type : '';
        }

        return $result;
    }

    /**
     * Converts all words of a string to first letter upper case and replaces a given separator by another one.
     *
     * @param string $str The search string
     * @param string $destSep The separator the string will contain afterwards
     * @param string $srcSep The separator being replaced
     * @return mixed
     */
    public function ucwords($str, $destSep = '_', $srcSep = '_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }
}