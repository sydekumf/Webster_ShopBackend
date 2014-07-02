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

namespace Webster\Shop\Entities;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;
use Doctrine\ORM\Mapping as ORM;

/**
 * Webster\Shop\Entities\Category
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
 * @MAP\ElasticSearchable(index="shop", type="category", source=true)
 */
class Category implements \JsonSerializable
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
     * @JMS\Type("string")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="no")
     */
    private $description;

    /**
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @MAP\ElasticField(type="boolean", includeInAll=false, index="no")
     */
    private $active;

    /**
     * @JMS\Type("string")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="no")
     */
    private $image;

    /**
     * @JMS\Type("array")
     * @JMS\Expose
     * @MAP\ElasticField(type="string", includeInAll=false, index="not_analyzed")
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="categories")
     */
    private $products;

    public function __construct($data)
    {
        if(is_object($data)){
            $data = get_object_vars($data);
        }

        $this->setId($data['id']);
        $this->setName($data['name']);
        $this->setDescription($data['description']);
        $this->setActive($data['active']);
        $this->setImage($data['image']);
        $this->setProducts($data['products']);
    }

    /**
     * Returns the category data as array
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result =  array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'active' => $this->getActive(),
            'image' => $this->getImage(),
            'products' => $this->getProducts()
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