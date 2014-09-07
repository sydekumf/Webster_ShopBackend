<?php

namespace Webster\Shop\Controllers\Catalog;

use Webster\Shop\Controllers\AbstractController;
use Webster\Shop\Messages\CategoryMessage;
use Webster\Shop\Entities\Category;
use Webster\Shop\Persistence\Catalog\CategoryProcessor;
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
class CategoryController extends AbstractController
{
    public function __construct(ConnectionInterface $connection, $settings)
    {
        parent::__construct($connection, $settings);
        $this->setProcessor(new CategoryProcessor($this->getSettings()));
    }

    public function getAction($content)
    {
        if($categoryId = $content->category_id){
            $categories = $this->getProcessor()->findById($categoryId);
        } else {
            $categories = $this->getProcessor()->findAll();
        }

        $categoryMessage = new CategoryMessage($categories);
        $categoryMessage->send($this->websocketConnection);
    }

    public function saveAction($content)
    {
        if(is_array($content)){
            $result = array();
            foreach($content as $category){
                $result[] = $this->saveCategory($category);
            }
        } else {
            $result = $this->saveCategory($content);
        }

        $categoryMessage = new CategoryMessage($result);
        $categoryMessage->send($this->websocketConnection);
    }

    private function saveCategory($data)
    {
        $category = new Category($data);
        return $this->getProcessor()->persist($category);
    }
}