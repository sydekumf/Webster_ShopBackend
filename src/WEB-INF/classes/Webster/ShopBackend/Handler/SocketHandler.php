<?php

namespace Webster\ShopBackend\Handler;

use Ratchet\ConnectionInterface;
use Symfony\Component\Process\Process;
use TechDivision\WebSocketContainer\Handlers\HandlerConfig;
use TechDivision\WebSocketContainer\Handlers\AbstractHandler;
use Webster\Shop\Handler\Dispatcher;
use Noodlehaus\Config;
use Webster\ShopBackend\Persistence\ProcessorFactory;

class SocketHandler extends AbstractHandler
{
    /**
     * The key for the context param with the name of the configuration file.
     *
     * @var string
     */
    const CONFIG_FILE_KEY = 'configFile';

    /**
     * @var  $dispatcher Dispatcher
     */
    protected $dispatcher;

    /**
     * @param HandlerConfig $config
     */
    public function init(HandlerConfig $handlerConfig)
    {
        error_log('SocketHandler, init');
        parent::init($handlerConfig);

        // get path to configuration file
        $configFile = $this->getApplication()->getWebappPath()
            . DIRECTORY_SEPARATOR
            . $this->getHandlerManager()->getInitParameter(self::CONFIG_FILE_KEY);

        // load the configuration file
        $config = new Config($configFile);

        // initialize dispatcher with current configuration
        $this->dispatcher = new Dispatcher($config, new ProcessorFactory($config));
    }

    /**
     * @see \Ratchet\ComponentInterface::onOpen()
     */
    public function onOpen(ConnectionInterface $connection)
    {
        error_log('SocketHandler, onOpen');

    }

    /**
     * @see \Ratchet\MessageInterface::onMessage()
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        $this->dispatcher->dispatchMessage($message, $connection);
    }

    /**
     * @see \Ratchet\ComponentInterface::onClose()
     */
    public function onClose(ConnectionInterface $connection)
    {

    }

    /**
     * @see \Ratchet\ComponentInterface::onError()
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        $connection->close();
    }
}