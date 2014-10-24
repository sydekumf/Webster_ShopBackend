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

namespace Webster\ShopBackend\Persistence\Catalog;

use Webster\ShopBackend\Entities\Product;
use Webster\ShopBackend\Persistence\AbstractProcessor;

/**
 * Webster\ShopBackend\Services\ProductProcessor
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
    public function findAll()
    {
        $dm = $this->getDocumentManager();

        $products = $dm->getRepository('Webster\ShopBackend\Entities\Product')
            ->findAll();

        return $products;
    }

    public function findById($id)
    {
        $dm = $this->getDocumentManager();
        $product = $dm->getRepository('Webster\ShopBackend\Entities\Product')->find($id);
        return $product;
    }

//    /**
//     * Returns all found products.
//     *
//     * @return array
//     */
//    public function findAll($ids = null)
//    {
//        $query = new \Elastica\Query();
//        $type = $this->getType();
//
//        if(is_array($ids)){
//            $idsFilter = new \Elastica\Filter\Ids($type, $ids);
//            $query->setFilter($idsFilter);
//        }
//
//        // query the product type
//        $productData = $type->search($query)->getResults();
//
//        $products = array();
//        foreach($productData as $entry){
//            $data = $entry->getData();
//            $data['id'] = $entry->getId();
//            $products[] = new Product($data);
//        }
//
//        return $products;
//    }

    /**
     * Persists the passed entity.
     *
     * @param mixed $product The entity to persist
     * @return Product The persisted entity
     */
    public function persist($product)
    {
        $dm = $this->getDocumentManager();

        if(is_array($product)){
            foreach($product as $p){
                $dm->persist($p);
            }
        } else if($product instanceof Product){
            $dm->persist($product);
        }

        $dm->flush();

        return $product;
    }

//    /**
//     * Returns all found products filtered by category id.
//     *
//     * @param $categoryId
//     */
//    public function findByCategoryId($categoryId)
//    {
//        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';
//
//        $sm = $this->getSearchManager();
//
//        $category = $sm->getRepository('Webster\ShopBackend\Entities\Category')->find($categoryId);
//        $productIds = $category->getProducts();
//
//        foreach($productIds as $id){
//            $products[] = $this->findById($id);
//        }
//
//        return $products;
//    }

//    /**
//     * Returns a product by its id.
//     *
//     * @param $productId
//     */
//    public function findById($productId)
//    {
//        $productData = $this->getType()->getDocument($productId);
//        $data = $productData->getData();
//        $data['id'] = $productData->getId();
//        return new Product($data);
//    }
}