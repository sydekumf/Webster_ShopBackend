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

namespace Webster\Shop\Services;

use Webster\Shop\Services\AbstractProcessor;
use Webster\Shop\Entities\Product;

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
 * @Singleton
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

        $productData = $product->toArray();
        // product id is not part of the document's content
        unset($productData['id']);

        $type = $this->getType();

        // create a document
        $productDocument = new \Elastica\Document('', $productData);

        // check if product already exists
        if(!$productId = $product->getId()){
            // product does not exist
            $this->validateEntity($product);
            $type->addDocument($productDocument);
        } else {
            // product already exists
            $this->validateEntity($product, array_keys($productData));
            $productDocument->setId($productId);
            $type->updateDocument($productDocument);
        }

        // Refresh Index
        $type->getIndex()->refresh();

        return $product;
    }

    /**
     * Returns all found products filtered by category id.
     *
     * @param $categoryId
     */
    public function findByCategoryId($categoryId)
    {
        $type = $this->getType();

        $term = new \Elastica\Query\Term();
        $term->setTerm('categories', $categoryId);
        $query = new \Elastica\Query();
        $query->setQuery($term);

        // query the product type
        $productsData = $type->search($query)->getResults();

        $products = array();
        foreach($productsData as $entry){
            $data = $entry->getData();
            $data['id'] = $entry->getId();
            $products[] = new Product($data);
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