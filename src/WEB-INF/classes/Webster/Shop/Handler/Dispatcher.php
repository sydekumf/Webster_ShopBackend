<?php

namespace Webster\Shop\Handler;

use Ratchet\ConnectionInterface;
use Webster\Shop\Messages\ResponseMessage;

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

        $classInfo = $this->_getClassInfo($message->type);
        $controllerClass = 'Webster\\Shop\\' . $classInfo['module'] .'\\Controllers\\' . $classInfo['type'] . 'Controller';
        $action = $message->action . 'Action';

        if(!class_exists($controllerClass)){
            throw new \Exception('Controller class "' . $controllerClass . '" could not be found.');
        }

        $settings = $this->getSettings();

        $controller = new $controllerClass($settings);
        $controller->$action($message->content, new ResponseMessage($connection));
    }

    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Extracts class information out of a given class id like module and type.
     *
     * @param string $classId
     * @return array
     */
    private function _getClassInfo($classId)
    {
        $result = array();

        // if class id is valid extract module and type
        if(strpos($classId, '/')){
            $classArr = explode('/', trim($classId));
            $module = $classArr[0];
            $type = !empty($classArr[1]) ? $classArr[1] : null;

            $result['module'] = $this->_ucwords($module, '\\');
            $result['type'] = $this->_ucwords($type, '\\');
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
    private function _ucwords($str, $destSep = '_', $srcSep = '_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }
}