<?php

namespace Webster\Shop\Controllers;

use TechDivision\PersistenceContainerClient\Context\Connection\Factory;
use Ratchet\ConnectionInterface;
use Webster\Shop\Handler\Dispatcher;

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
     * @var  $connection Holds the connection of the persistence container socket
     */
    protected $persistenceConnection;

    /**
     * @var  $session Holds the persistence container socket session
     */
    protected $session;

    /**
     * @var  $webSocketConnection Holds the connection of the websocket
     */
    protected $websocketConnection;

    /**
     * Constructor which takes the websocket connection to the client.
     *
     * @param ConnectionInterface $con
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->websocketConnection = $connection;

        $this->persistenceConnection = Factory::createContextConnection();
        $this->session = $this->persistenceConnection->createContextSession();
    }

    /**
     * Returns a proxy class for a given class name.
     *
     * @param $class The class name
     * @return mixed
     */
    public function getProxy($class)
    {
        $initialContext = $this->session->createInitialContext();
        return $initialContext->lookup($class);
    }
}