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

namespace Webster\Shop\Services;

use Webster\Shop\Services\AbstractProcessor;
use Webster\Shop\Entities\Category;

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
 * @Singleton
 */
class CategoryProcessor extends AbstractProcessor
{
    const ELASTIC_TYPE = 'category';

    /**
     * Returns all found categories.
     *
     * @return array
     */
    public function findAll()
    {
        $type = $this->getType();

        // query the category type
        $categoryData = $type->search()->getResults();

        $categories = array();
        foreach($categoryData as $entry){
            $data = $entry->getData();
            $data['id'] = $entry->getId();
            $categories[] = new Category($data);
        }

        return $categories;
    }

    /**
     * Persists the passed entity.
     *
     * @param Category $category The entity to persist
     * @return Category The persisted entity
     */
    public function persist(Category $category)
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        $categoryData = $category->toArray();
        // category id is not part of the document's content
        unset($categoryData['id']);

        $type = $this->getType();

        // create a document
        $categoryDocument = new \Elastica\Document('', $categoryData);

        // check if category already exists
        if(!$categoryId = $category->getId()){
            // category does not exist
            $this->validateEntity($category);
            $type->addDocument($categoryDocument);
        } else {
            // category already exists
            $this->validateEntity($category, array_keys($categoryData));
            $categoryDocument->setId($categoryId);
            $type->updateDocument($categoryDocument);
        }

        // Refresh Index
        $type->getIndex()->refresh();

        return $category;
    }

    /**
     * Returns a category by its id.
     *
     * @param $categoryId
     */
    public function findById($categoryId)
    {
        $categoryData = $this->getType()->getDocument($categoryId);
        $data = $categoryData->getData();
        $data['id'] = $categoryData->getId();
        return new Category($data);
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