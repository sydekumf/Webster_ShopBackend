<?php

namespace Webster\Shop\Messages;

use Webster\Shop\Messages\AbstractMessage;
use Ratchet\ConnectionInterface;
use Webster\Shop\Entities\Category;

/**
 * The message handling categories.
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
class CategoryMessage extends AbstractMessage
{
    /**
     * @var  $categories Holds the categories
     */
    public $categories;

    /**
     * Constructor which takes one or more categories.
     *
     * @param $category mixed Takes one category or more in an array
     */
    public function __construct($category)
    {
        $this->categories = array();

        // set the product
        if($category instanceof Category){
            $this->addCategory($category);
        } else if(is_array($category)){
            foreach($category as $_category){
                if(!$_category instanceof Category){
                    throw new \Exception('CategoryMessage is not compatible for ' . get_class($category));
                }
                $this->addCategory($_category);
            }
        } else {
            throw new \Exception('CategoryMessage is not compatible for ' . get_class($category));
        }
    }

    /**
     * Adds a category
     *
     * @param $category Category
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;
    }

    /**
     * Sends the necessary data to the client.
     *
     * @param ConnectionInterface $connection
     */
    public function send(ConnectionInterface $connection)
    {
        $result = new \stdClass();
        $result->type = $this->getMessageTyp();
        $result->content = array();

        foreach($this->categories as $category){
            $result->content[] = $category->toArray();
        }

        $connection->send(json_encode($result));
    }
}