<?php
/**
 * Category processor class
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

use Webster\Shop\Entities\Category;
use Webster\Shop\Persistence\AbstractProcessor;

/**
 * Webster\Shop\Services\CategoryProcessor
 *
 * Category processor class
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
class CategoryProcessor extends AbstractProcessor
{
    public function findAll()
    {
        $dm = $this->getDocumentManager();

        $categories = $dm->getRepository('Webster\Shop\Entities\Category')
            ->findAll()
            ->toArray(false);

        foreach($categories as $c){
            $products = $c->getProducts();
//            foreach($products as $p){
//                error_log($p->getName());
//            }
        }
//        error_log(var_export($categories, true));

//        $c = $dm->getRepository('Webster\Shop\Entities\Category')
//            ->find('53d943d722b58320444f05bc');

//        $a = $c->getProducts();
//        foreach($a as $product){
//            error_log($product->getName());
//        }
//        error_log('oida');

//        return $categories;
    }

//    /**
//     * Returns all found categories.
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
//        // query the category type
//        $categoryData = $type->search($query)->getResults();
//
//        $categories = array();
//        foreach($categoryData as $entry){
//            $data = $entry->getData();
//            $data['id'] = $entry->getId();
//            $categories[] = new Category($data);
//        }
//
//        return $categories;
//    }

    /**
     * Persists the passed entity.
     *
     * @param Category $category The entity to persist
     * @return Category The persisted entity
     */
    public function persist(Category $category)
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        /* @var $sm Doctrine\Search\SearchManager */
        $sm = $this->getSearchManager();

        $sm->persist($category);
        $sm->flush();

        return $category;
    }

//    /**
//     * Returns a category by its id.
//     *
//     * @param $categoryId
//     */
//    public function findById($categoryId)
//    {
//        $categoryData = $this->getType()->getDocument($categoryId);
//        $data = $categoryData->getData();
//        $data['id'] = $categoryData->getId();
//        return new Category($data);
//    }

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