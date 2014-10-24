<?php
/**
 * A product entity
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    Webster\Shop
 * @subpackage Entities
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

namespace Webster\ShopBackend\Entities;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Webster\ShopBackend\Entities\Product
 *
 * A product entity
 *
 * @category   AppServer
 * @package    Webster\Shop
 * @subpackage Entities
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */
/** @ODM\Document */
class Product implements \JsonSerializable
{
    /** @ODM\Id */
    private $id;

    /** @ODM\String */
    private $name;

    /** @ODM\Float */
    private $price;

    /** @ODM\Int */
    private $inventory;

    /** @ODM\String */
    private $description;

    /** @ODM\String */
    private $image;

    /** @ODM\Boolean */
    private $active;

    /** @ODM\ReferenceMany(targetDocument="Category", mappedBy="products", simple=true) */
    private $categories;

    public function __construct($data)
    {
        $this->categories = array();

        if(is_object($data)){
            $data = get_object_vars($data);
        }

        $this->setId($data['id']);
        $this->setName($data['name']);
        $this->setPrice($data['price']);
        $this->setInventory($data['inventory']);
        $this->setDescription($data['description']);
        $this->setImage($data['image']);
        $this->setActive($data['active']);
        $this->addCategories($data['categories']);
    }

    /**
     * Returns the product data as array
     *
     * @return array
     */
    public function jsonSerialize()
    {
//        $categories = array();
//        foreach($this->getCategories() as $category){
//            $categories[] = $category;
//        }

        $result =  array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'inventory' => $this->getInventory(),
            'description' => $this->getDescription(),
            'image' => $this->getImage(),
            'active' => $this->getActive(),
            'categories' => $this->getCategories()
        );

        // delete null entries
        foreach($result as $key => $value){
            if(is_null($value)){
                unset($result[$key]);
            }
        }
        return $result;
    }

    public function addCategory(Category $category)
    {
        $categoryId = $category->getId();
        if(!array_key_exists($categoryId, $this->categories)){
            $this->categories[$category->getId()] = $category;
        }
    }

    public function addCategories($categories)
    {
        if(is_array($categories)){
            foreach($categories as $category){
                if($category instanceof Category){
                    $this->addCategory($category);
                }
            }
        }
    }

    public function removeCategory(Category $category)
    {
        $categoryId = $category->getId();
        if(array_key_exists($categoryId, $this->categories)){
            unset($this->categories[$categoryId]);
        }
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param int $inventory
     */
    public function setInventory($inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * @return int
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }
}