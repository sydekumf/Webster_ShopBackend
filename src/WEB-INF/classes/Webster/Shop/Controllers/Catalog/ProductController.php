<?php

namespace Webster\Shop\Controllers\Catalog;

use Webster\Shop\Controllers\AbstractController;
use Webster\Shop\Messages\ProductMessage;
use Webster\Shop\Entities\Product;
use Webster\Shop\Persistence\Catalog\ProductProcessor;
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
class ProductController extends AbstractController
{
    public function __construct(ConnectionInterface $connection, $settings)
    {
        parent::__construct($connection, $settings);
        $this->setProcessor(new ProductProcessor($this->getSettings()));
    }

    public function getAction($content)
    {
        if($categoryId = $content->category_id){
            $products = $this->getProcessor()->findByCategoryId($categoryId);
        } else if($productId = $content->product_id){
            $products = $this->getProcessor()->findById($productId);
        } else {
            $products = $this->getProcessor()->findAll();
        }

        $productMessage = new ProductMessage($products);
        $productMessage->send($this->websocketConnection);
    }

    public function saveAction($content)
    {
        if(is_array($content)){
            $result = array();
            foreach($content as $product){
                $result[] = $this->saveProduct($product);
            }
        } else {
            $result = $this->saveProduct($content);
        }

        $productMessage = new ProductMessage($result);
        $productMessage->send($this->websocketConnection);
    }

    private function saveProduct($data)
    {
        $product = new Product($data);
        return $this->getProcessor()->persist($product);
    }
}