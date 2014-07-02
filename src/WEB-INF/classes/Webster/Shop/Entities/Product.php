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

namespace Webster\Shop\Entities;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;
use Doctrine\ORM\Mapping as ORM;

/**
 * Webster\Shop\Entities\Product
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
/**
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(index="shop", type="product", source=true)
 */
class Product implements \JsonSerializable
{
    /**
     * @MAP\Id
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="no")
     */
    private $name;

    /**
     * @JMS\Type("double")
     * @JMS\Expose
     * @MAP\ElasticField(type="float", includeInAll=false, index="no")
     */
    private $price;

    /**
     * @JMS\Type("integer")
     * @JMS\Expose
     * @MAP\ElasticField(type="integer", includeInAll=false, index="no")
     */
    private $inventory;

    /**
     * @JMS\Type("string")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="no")
     */
    private $description;

    /**
     * @JMS\Type("string")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="no")
     */
    private $image;

    /**
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @MAP\ElasticField(type="boolean", includeInAll=false, index="no")
     * @ORM\ManyToMany(targetEntity="Webster\Shop\Entities\ROFFEL", mappedBy="oida")
     */
    private $active;

    /**
     * @JMS\Type("array")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="not_analyzed")
     * @ORM\ManyToMany(targetEntity="Webster\Shop\Entities\Category", mappedBy="products")
     */
    private $categories;

    public function __construct($data)
    {
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
        $this->setCategories($data['categories']);
    }

    /**
     * Returns the product data as array
     *
     * @return array
     */
    public function jsonSerialize()
    {
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