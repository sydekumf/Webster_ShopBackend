<?php

namespace Webster\ShopBackend\Controllers\Catalog;

use Webster\ShopBackend\Controllers\AbstractController;
use Webster\ShopBackend\Messages\ResponseMessage;
use Webster\ShopBackend\Persistence\Catalog\CategoryProcessor;

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
class CategoryController extends AbstractController
{
    /**
     * @var CategoryProcessor $_categoryProcessor Holds the category processor
     */
    private $_categoryProcessor;

    /**
     * @inherit
     */
    protected function _init()
    {
        $this->_categoryProcessor = $this->getProcessorFactory()->get('category');
    }

    public function indexAction($content, ResponseMessage $message)
    {
        $categories = $this->_categoryProcessor->findAll();

        $message->addContent('products', $categories)
            ->send();
    }

//    public function getAction($content)
//    {
//        if($categoryId = $content->category_id){
//            $categories = $this->getProcessor()->findById($categoryId);
//        } else {
//            $categories = $this->getProcessor()->findAll();
//        }
//
//        $categoryMessage = new CategoryMessage($categories);
//        $categoryMessage->send($this->websocketConnection);
//    }
//
//    public function saveAction($content)
//    {
//        if(is_array($content)){
//            $result = array();
//            foreach($content as $category){
//                $result[] = $this->saveCategory($category);
//            }
//        } else {
//            $result = $this->saveCategory($content);
//        }
//
//        $categoryMessage = new CategoryMessage($result);
//        $categoryMessage->send($this->websocketConnection);
//    }
//
//    private function saveCategory($data)
//    {
//        $category = new Category($data);
//        return $this->getProcessor()->persist($category);
//    }
}