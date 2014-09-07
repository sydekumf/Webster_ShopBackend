<?php

namespace Webster\ShopBackend\Catalog\Controllers;

use Webster\Shop\Controllers\AbstractController;
use Webster\Shop\Entities\Category;
use Webster\Shop\Messages\ProductMessage;
use Webster\Shop\Messages\CategoryMessage;
use Webster\Shop\Entities\Product;
use Webster\Shop\Persistence\Catalog\CategoryProcessor;
use Webster\Shop\Persistence\Catalog\ProductProcessor;
use Webster\Shop\Messages\ResponseMessage;

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
    public function __construct($settings)
    {
        parent::__construct($settings);
        $this->setProcessor(new ProductProcessor($this->getSettings()));
    }

    public function saveAction($content, ResponseMessage $message)
    {
        $productData = $content->product;
        $product = new Product($productData);

        $categoryIds = array();
        foreach($content->categories as $category){
            $categoryIds[] = $category->id;
        }

        $product->setCategories($categoryIds);
        if($id = $productData->id){
            $product = $this->getProcessor()->update($product);
        } else {
            $product = $this->getProcessor()->persist($product);
        }

        if($product){
            error_log('success');
            $content = 'The product has been saved.';
        } else {
            error_log('no success');
            $content = 'The product could not been saved.';
        }

        $message->addContent('notify', $content);
        $message->send();
    }

    public function indexAction($content, ResponseMessage $message)
    {
        $products = $this->getProcessor()->findAll();

        $message->addContent('products', $products)
            ->send();
    }

    public function editAction($content, ResponseMessage $message)
    {
        $categoryProcessor = new CategoryProcessor($this->getSettings());
        $categories = $categoryProcessor->findAll();

        $message->addContent('categories', $categories);

        if($productId = $content->product_id){
            $product = $this->getProcessor()->findById($productId);
            $message->addContent('product', $product);
        }

        $message->send();
    }
}