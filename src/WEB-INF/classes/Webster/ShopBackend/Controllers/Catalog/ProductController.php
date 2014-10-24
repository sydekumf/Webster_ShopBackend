<?php

namespace Webster\ShopBackend\Controllers\Catalog;

use Webster\ShopBackend\Controllers\AbstractController;
use Webster\ShopBackend\Messages\ResponseMessage;
use Webster\ShopBackend\Persistence\Catalog\ProductProcessor;
use Webster\ShopBackend\Persistence\Catalog\CategoryProcessor;
use Webster\ShopBackend\Entities\Product;
use Webster\ShopBackend\Entities\Category;
use Doctrine\ODM\MongoDB\PersistentCollection;

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
    /**
     * @var ProductProcessor $_productProcessor Holds the product processor.
     */
    private $_productProcessor;

    /**
     * @var CategoryProcessor $_categoryProcessor Holds the category processor.
     */
    private $_categoryProcessor;

    /**
     * @inherit
     */
    protected function _init()
    {
        $this->_productProcessor = $this->getProcessorFactory()->get('catalog/product');
        $this->_categoryProcessor = $this->getProcessorFactory()->get('catalog/category');
    }

    public function indexAction($content, ResponseMessage $message)
    {
        $products = $this->_productProcessor->findAll();

        $message->addContent('products', $products)
            ->send();
    }

    public function saveAction($content, ResponseMessage $message)
    {
        $productData = $content->product;
        $product = new Product($productData);

//        $categories = array();
//        $ids = array();
        foreach($content->categories as $categoryData){
            $category = $this->_categoryProcessor->find($categoryData->id);
            /* @var $products PersistentCollection */
            $products = $category->getProducts();




            if($categoryData->checked){
                $products->set($product->getId(), $product);
            } else {
                foreach($products as $p){
                    if($product->getId() == $p->getId()){
                        $products->removeElement($p);
                    }
                }
            }

//            $category->setProducts($products);
            $this->_categoryProcessor->persist($category);

//            error_log(var_export($categoryData, true));
//            $category = new Category($categoryData);
//            if($categoryData->checked){
//                $category->addProduct($product);
//            } else {
//                $category->removeProduct($product);
//            }
//
//            $categories[] = $category;
        }
//        $product = $this->_productProcessor->persist($product);

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

    /**
     * Edit action which gets called in order to edit or create a product.
     *
     * @param \stdClass $content
     * @param ResponseMessage $message
     */
    public function editAction($content, ResponseMessage $message)
    {
        // init product and categories
        $product = null;
        $categoryData = array();

        // if a product id is given, we are in edit mode
        if($productId = $content->product_id){
            // get the product by id and add it to response
            $product = $this->_productProcessor->findById($productId);
            $message->addContent('product', $product);
        }

        // for every category
        /* @var $category Category */
        foreach($this->_categoryProcessor->findAll() as $category){
            // get category's product ids
            $productIds = $category->getProductIds();

            // if we are in edit mode and category contains product id
            if($productId && in_array($productId, $productIds)){
                // the category is checked
                $checked = true;
            } else {
                // the category is unchecked, as the product does not belong to it or is new
                $checked = false;
            }

            // build up category data
            $categoryData[] = array(
                'id'      => $category->getId(),
                'name'    => $category->getName(),
                'checked' => $checked
            );
        }

        // add category data to response
        $message->addContent('categories', $categoryData);

        // send response
        $message->send();
    }
}