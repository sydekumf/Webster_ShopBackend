<?php

namespace Webster\Shop\Controllers;

use TechDivision\PersistenceContainerClient\Context\Connection\Factory;
use Ratchet\ConnectionInterface;
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
     * @var  $webSocketConnection Holds the connection of the websocket
     */
    protected $websocketConnection;

    /**
     * @var  $processor AbstractProcessor
     */
    protected $processor;

    /**
     * Constructor which takes the websocket connection to the client.
     *
     * @param ConnectionInterface $con
     */
    public function __construct(ConnectionInterface $connection, AbstractProcessor $processor)
    {
        $this->websocketConnection = $connection;
        $this->processor = $processor;
    }

    public function getProcessor()
    {
        return $this->processor;
    }
}