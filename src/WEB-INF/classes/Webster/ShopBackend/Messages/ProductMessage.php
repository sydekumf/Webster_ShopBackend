<?php

namespace Webster\ShopBackend\Messages;

use Webster\ShopBackend\Messages\AbstractMessage;
use Ratchet\ConnectionInterface;
use Webster\ShopBackend\Entities\Product;

/**
 * The message handling products.
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
class ProductMessage extends AbstractMessage
{
    /**
     * @var  $products Holds the products
     */
    public $products;

    /**
     * Constructor which takes one or more products.
     *
     * @param $product mixed Takes one product or more in an array
     */
    public function __construct($product)
    {
        $this->products = array();

        // set the product
        if($product instanceof Product){
            $this->addProduct($product);
        } else if(is_array($product)){
            foreach($product as $_product){
                if(!$_product instanceof Product){
                    throw new \Exception('ProductMessage is not compatible for ' . get_class($product));
                }
                $this->addProduct($_product);
            }
        } else {
            throw new \Exception('ProductMessage is not compatible for ' . get_class($product));
        }
    }

    /**
     * Adds a product
     *
     * @param $product Product
     */
    public function addProduct($product)
    {
        $this->products[] = $product;
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

        foreach($this->products as $product){
            $result->content[] = $product->toArray();
        }

        $connection->send(json_encode($result));
    }
}