<?php

namespace Webster\ShopBackend\Messages;

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
class ResponseMessage
{
    /**
     * @var  $_connection ConnectionInterface The connection this message gets send over
     */
    protected $_connection;

    /**
     * @var array $_content Content of this message
     */
    protected $_content;

    /**
     * Constructs a message
     */
    public function __construct(ConnectionInterface $connection, $content = array())
    {
        $this->_connection = $connection;
        $this->_content = $content;
    }

    /**
     * Adds content to this message, defined by a key
     *
     * @param $key string The key defining the content
     * @param $content mixed The content being added to this message
     */
    public function addContent($key, $content)
    {
        $this->_content[$key] = $content;

        return $this;
    }

    /**
     * Sets the content of this message and replaces old content.
     *
     * @param $content array The content of this message
     */
    public function setContent($content)
    {
        if(is_array($content)){
            $this->_content = $content;
        }

        return $this;
    }

    /**
     * Returns the content of this message
     *
     * @return array
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Returns the connection of this message
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Sends this message via the given connection serialized as json
     */
    public function send()
    {
        $result = new \stdClass();
        $result->content = $this->getContent();

        error_log('Sending message:');
        error_log(var_export(json_encode($result), true));

        $this->getConnection()->send(json_encode($result));
    }
}