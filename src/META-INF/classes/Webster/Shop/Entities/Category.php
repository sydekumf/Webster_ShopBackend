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

use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

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
class Category
{
    const ELASTIC_TYPE = 'category';

    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = "2",
     *      max = "50",
     *      minMessage = "The name must be at least {{ limit }} characters length",
     *      maxMessage = "The name cannot be longer than {{ limit }} characters length"
     * )
     */
    private $name;

    private $description;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
     */
    private $active;

    private $image;
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

    public function getElasticType($index)
    {
        return $index->getType(self::ELASTIC_TYPE);
    }

    public static function createMapping($elasticaIndex)
    {
        require_once '/opt/appserver/webapps/webstershop/vendor/autoload.php';

        //Create a type
        $elasticaType = $elasticaIndex->getType(self::ELASTIC_TYPE);

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setParam('index_analyzer', 'indexAnalyzer');
        $mapping->setParam('search_analyzer', 'searchAnalyzer');

        // Send mapping to type
        $mapping->send();
    }

    /**
     * Returns the category data as array
     *
     * @return array
     */
    public function toArray()
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
            if(!$value){
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