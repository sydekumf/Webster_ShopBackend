<?php

namespace Webster\Shop\Messages;

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
class AbstractMessage
{
    public function send(ConnectionInterface $connection)
    {
        $result = new \stdClass();
        $result->type = $this->getMessageTyp();
        $result->content = get_object_vars($this);

        $connection->send(json_encode($result));
    }

    public function getMessageTyp()
    {
        $className = explode('\\', get_class($this));
        return end($className);
    }
}