<?php

namespace Webster\Shop\Handler;

use Ratchet\ConnectionInterface;
use TechDivision\ApplicationServer\Interfaces\ApplicationInterface;

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
     * @var $settings array Holds the app settings
     */
    protected $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function dispatchMessage($message, ConnectionInterface $connection)
    {
        if( !($message = json_decode($message)) ){
            throw new \Exception('The message could not be deserialized correctly.');
        }
        if( (!$message->type) || (!$message->action) ){
            throw new \Exception('The message is not correct.');
        }

        $controllerClass = 'Webster\\Shop\\Controllers\\' . $message->type . 'Controller';
        $persistenceClass = 'Webster\\Shop\\Persistence\\' . $message->type . 'Processor';
        $action = $message->action . 'Action';

        if(!class_exists($controllerClass) || !class_exists($persistenceClass)){
            throw new \Exception('Controller or persistence class could not be found.' . $persistenceClass);
        }

        $settings = $this->getSettings();

        if(!array_key_exists('elasticsearch', $settings)){
            throw new \Exception('No connection parameters for elasticsearch found.');
        }

        $processor = new $persistenceClass($settings['elasticsearch']);
        $controller = new $controllerClass($connection, $processor);
        $controller->$action($message->content);
    }

    public function getSettings()
    {
        return $this->settings;
    }
}