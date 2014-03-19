<?php

namespace Webster\Shop\Handler;
use Ratchet\ConnectionInterface;

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
     * @var  $webappPath Holds the webapp path
     */
    protected $webappPath;

    public function __construct($webappPath)
    {
        $this->webappPath = $webappPath;
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
        $action = $message->action . 'Action';

        if(!class_exists($controllerClass)){
            throw new \Exception('The message could not be processed.');
        }
        $controller = new $controllerClass($connection);
        $controller->$action($message->content);
    }

    public function getWebappPath()
    {
        return $this->webappPath;
    }
}