<?php
/**
 * Product processor class
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    Webster\Shop
 * @subpackage Services
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */

namespace Webster\Shop\Persistence\Catalog;

use Webster\Shop\Entities\Product;
use Webster\Shop\Persistence\AbstractProcessor;

/**
 * Webster\Shop\Services\ProductProcessor
 *
 * Product processor class
 *
 * @category   AppServer
 * @package    Webster\Shop
 * @subpackage Services
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */
class ProductProcessor extends AbstractProcessor
{
    const ELASTIC_TYPE = 'product';

    /**
     * Returns all found products.
     *
     * @return array
     */
    public function findAll()
    {
        $type = $this->getType();

        // query the product type
        $productsData = $type->search()->getResults();

        $products = array();
        foreach($productsData as $entry){
            $data = $entry->getData();
            $data['id'] = $entry->getId();
            $products[] = new Product($data);
        }

        return $products;
    }

    /**
     * Persists the passed entity.
     *
     * @param Product $product The entity to persist
     * @return Product The persisted entity
     */
    public function persist(Product $product)
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        /* @var $sm Doctrine\Search\SearchManager */
        $sm = $this->getSearchManager();

        $sm->persist($product);
        $sm->flush();

        return $product;
    }

    /**
     * Returns all found products filtered by category id.
     *
     * @param $categoryId
     */
    public function findByCategoryId($categoryId)
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        $sm = $this->getSearchManager();

        $category = $sm->getRepository('Webster\Shop\Entities\Category')->find($categoryId);
        $productIds = $category->getProducts();

        foreach($productIds as $id){
            $products[] = $this->findById($id);
        }

        return $products;
    }

    /**
     * Returns a product by its id.
     *
     * @param $productId
     */
    public function findById($productId)
    {
        $productData = $this->getType()->getDocument($productId);
        $data = $productData->getData();
        $data['id'] = $productData->getId();
        return new Product($data);
    }

    /**
     * Returns the elasticsearch index.
     *
     * @return \Elastica\Index
     */
    protected function getIndex()
    {
        $elastica = $this->getElasticaClient();
        return $elastica->getIndex(self::ELASTIC_INDEX);
    }

    /**
     * Returns the elasticsearch type.
     *
     * @return \Elastica\Type
     */
    protected function getType()
    {
        $index = $this->getIndex();
        return $index->getType(self::ELASTIC_TYPE);
    }
}