<?php

namespace Webster\Shop\Controllers\Catalog;

use Webster\Shop\Controllers\AbstractController;
use Webster\Shop\Messages\ProductMessage;
use Webster\Shop\Entities\Product;

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
    const PROXY_CLASS = 'Webster\Shop\Services\ProductProcessor';

    public function getAllAction($content)
    {
        if($categoryId = $content->category_id){
            $products = $this->getProxy(self::PROXY_CLASS)->findByCategoryId($categoryId);
        } else {
            $products = $this->getProxy(self::PROXY_CLASS)->findAll();
        }

        $productMessage = new ProductMessage($products);
        $productMessage->send($this->websocketConnection);
    }

    public function getAction($content)
    {
        $productId = $content->product_id;
        $product = $this->getProxy(self::PROXY_CLASS)->findById($productId);
        $productMessage = new ProductMessage($product);
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
        return $this->getProxy(self::PROXY_CLASS)->persist($product);
    }
}