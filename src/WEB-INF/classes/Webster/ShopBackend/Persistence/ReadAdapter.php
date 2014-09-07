<?php

namespace Webster\Shop\Persistence;

/**
 * <REPLACE WITH FILE DESCRIPTION>
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    $package
 * @subpackage $subPackage
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */
class ReadAdapter
{
    protected $_client = null;

    protected $_index = null;

    public function __construct($elasticaClient, $indexName)
    {
        $this->_client = $elasticaClient;
        $this->_index = $this->_client->getIndex($indexName);
    }

    /**
     * Returns all found entities optionally filtered by an array of ids.
     *
     * @return array
     */
    public function findAll($entityClassName, $type, $ids = null)
    {
        if( !($type = $this->getType($type)) ){
            return array();
        }

        $query = new \Elastica\Query();

        if(is_array($ids)){
            $idsFilter = new \Elastica\Filter\Ids($type, $ids);
            $query->setFilter($idsFilter);
        }

        // query the type
        $entityData = $type->search($query)->getResults();

        $entities = array();
        foreach($entityData as $entry){
            $data = $entry->getData();
            $data['id'] = $entry->getId();
            $entities[] = new $entityClassName($data);
        }

        return $entities;
    }

    /**
     * Returns an entity by its id.
     *
     * @param $productId
     */
    public function findById($entityClassName, $type, $id)
    {
        $entity = $this->getType($type)->getDocument($id);
        $entityData = $entity->getData();
        $entityData['id'] = $entity->getId();

        return new $entityClassName($entityData);
    }

    /**
     * Returns the elasticsearch index.
     *
     * @return \Elastica\Index
     */
    protected function getIndex()
    {
        return $this->_index;
    }

    /**
     * Returns the elasticsearch type.
     *
     * @return \Elastica\Type
     */
    protected function getType($typeName)
    {
        $index = $this->getIndex();
        return $index->getType($typeName);
    }
}