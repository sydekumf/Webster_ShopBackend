<?php
/**
 * A category entity
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
 * Webster\ShopBackend\Entities\Category
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
class Category implements \JsonSerializable
{
    /** @ODM\Id */
    private $id;

    /** @ODM\String */
    private $name;

    /** @ODM\String */
    private $description;

    /** @ODM\Boolean */
    private $active;

    /** @ODM\String */
    private $image;

    /** @ODM\ReferenceMany(targetDocument="Product", inversedBy="categories", simple=true, strategy="addToSet") */
    private $products;

    public function __construct($data)
    {
        $this->products = array();

        if(is_object($data)){
            $data = get_object_vars($data);
        }

        $this->setId($data['id']);
        $this->setName($data['name']);
        $this->setDescription($data['description']);
        $this->setActive($data['active']);
        $this->setImage($data['image']);
        $this->addProducts($data['products']);
    }

    /**
     * Returns the category data as array
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $products = array();
        foreach($this->getProducts() as $product){
            $products[] = $product;
        }

        $result =  array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'active' => $this->getActive(),
            'image' => $this->getImage(),
            'products' => $products
        );

        // delete null entries
        foreach($result as $key => $value){
            if(is_null($value)){
                unset($result[$key]);
            }
        }
        return $result;
    }

    public function addProduct(Product $product)
    {
        $productId = $product->getId();
        if(!array_key_exists($productId, $this->products)){
            $this->products[$product->getId()] = $product;
        }
    }

    public function addProducts($products)
    {
        if(is_array($products)){
            foreach($products as $product){
                if($product instanceof Product){
                    $this->addProduct($product);
                }
            }
        }
    }

    public function removeProduct(Product $product)
    {
        $productId = $product->getId();
        if(array_key_exists($productId, $this->products)){
            unset($this->products[$productId]);
        }
    }

    public function getProductIds()
    {
        $products = $this->getProducts();
        $mongoData = $products->getMongoData();
        $productIds = array();

        foreach($mongoData as $mongoId){
            $productIds[] = $mongoId->{'$id'};
        }

        return $productIds;
    }

    /**
     * @param mixed $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
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
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }
}