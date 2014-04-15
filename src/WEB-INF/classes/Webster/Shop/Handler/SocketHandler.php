<?php

namespace Webster\Shop\Handler;

use Ratchet\ConnectionInterface;
use TechDivision\WebSocketContainer\Handlers\HandlerConfig;
use TechDivision\WebSocketContainer\Handlers\AbstractHandler;
use Webster\Shop\Handler\Dispatcher;

class SocketHandler extends AbstractHandler
{
    /**
     * The key for the context param with the name of the settings file.
     *
     * @var string
     */
    const SETTINGS_FILE = 'settingsFile';

    /**
     * @var  $dispatcher Dispatcher
     */
    protected $dispatcher;

    /**
     * @param HandlerConfig $config
     */
    public function init(HandlerConfig $config)
    {
        error_log('SocketHandler, init');
        parent::init($config);

        $settingsFile = $this->getHandlerManager()->getInitParameter(self::SETTINGS_FILE);
        $settings = parse_ini_file(
            $this->getApplication()->getWebappPath() . DIRECTORY_SEPARATOR . $settingsFile,
            true
        );
        $this->dispatcher = new Dispatcher($settings);
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