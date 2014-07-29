<?php

namespace Webster\Shop\Persistence;

use \Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use \Doctrine\Search\Events;
use \Doctrine\Search\Event\LifecycleEventArgs;
use Doctrine\Search\Configuration;
use Doctrine\Common\EventManager;
use Doctrine\Search\SearchManager;
use Webster\Shop\Persistence\AnnotationDriver;
use Doctrine\Search\ElasticSearch\Client as ElasticaAdapter;
use Doctrine\Search\Mapping\ClassMetadata;

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
class RelationHandler implements EventSubscriber
{
    protected $_sm = null;

    protected $_readAdapter = null;

    public function __construct(ReadAdapter $adapter)
    {
        // TODO: Refactor this, maybe annotations can be loaded somehow different

        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        //Annotation metadata driver
        $config = new Configuration();
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $md = new AnnotationDriver($reader, array());
        $config->setMetadataDriverImpl($md);

        // get the search manager
        $this->_sm = new SearchManager(
            $config,
            new ElasticaAdapter(new \Elastica\Client()),
            new EventManager()
        );

        $this->_readAdapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(Events::prePersist);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        /* @var $md ClassMetadata */
        $md = $this->_sm->getClassMetadata(get_class($entity));

        foreach($md->fieldMappings as $mapping){
            if($mapping instanceof ManyToMany){
                $this->_handleManyToMany($args->getSearchManager(), $args->getEntity(), $md);
            } else if($mapping instanceof ManyToOne){
                $this->_handleManyToOne($args->getSearchManager(), $args->getEntity(), $md);
            } else if($mapping instanceof OneToMany){
                $this->_handleOneToMany($args->getSearchManager(), $args->getEntity(), $md);
            }
        }
    }

    protected function _handleManyToMany(SearchManager $sm, $entity, ClassMetadata $md)
    {
        $searchMd = $sm->getClassMetadata(get_class($entity));
        $type = $searchMd->type;



        $oldEntity = $this->getReadAdapter()
            ->findById(get_class($entity), $type, $entity->getId());

        foreach($md->fieldMappings as $thisAttributeName => $data){
            $otherAttributeName = null;
            if(isset($data->mappedBy)){
                $otherAttributeName = $data->mappedBy;
            } else if (isset($data->inversedBy)){
                $otherAttributeName = $data->inversedBy;
            }

            $targetEntity = $data->targetEntity;
            $oldIds = $this->_read($oldEntity, $thisAttributeName);
            $entityIds = $this->_read($entity, $thisAttributeName);

            if(!is_array($oldIds)){
                $deleteIds = array();
                $newIds = $entityIds;
            }
            if(!is_array($entityIds)){
                $newIds = array();
                $deleteIds = $oldIds;
            }
            if(is_array($oldIds) && is_array($entityIds)){
                $deleteIds = array_diff($oldIds, $entityIds);
                $newIds = array_diff($entityIds, $oldIds);
            }

            $type = $sm->getClassMetadata(get_class($entity))->type;

            $className = $this->_className($md->className);

            if(!empty($deleteIds)){
                foreach($this->getReadAdapter()->findAll($targetEntity, $type, $deleteIds) as $deleteEntity){
                    $holder = $this->_read($deleteEntity, $otherAttributeName);

                    if( ($key = array_search($entity->getId(), $holder)) ){
                        unset($holder[$key]);
                    }

                    $this->_write($deleteEntity, $otherAttributeName, $holder);

                    $sm->persist($deleteEntity);
                }
            }

            error_log(var_export($newIds, true));
            if(!empty($newIds)){
                foreach($this->getReadAdapter()->findAll($targetEntity, $type, $newIds) as $newEntity){
                    error_log(var_export($newEntity, true));
                    $holder = $this->_read($newEntity, $otherAttributeName);

                    $holder[] = $entity->getId();

                    error_log(var_export($newEntity, true));
                    $this->_write($newEntity, $otherAttributeName, $holder);
                    $sm->persist($newEntity);
                }
            }
        }




//        Doctrine\Search\Mapping\ClassMetadata::__set_state(array(
//            'index' => NULL,
//            'type' => NULL,
//            'numberOfShards' => 1,
//            'numberOfReplicas' => 0,
//            'opType' => 1,
//            'parent' => NULL,
//            'timeToLive' => 1,
//            'value' => 1,
//            'source' => true,
//            'boost' => NULL,
//            'className' => 'Webster\\Shop\\Entities\\Product',
//            'fieldMappings' =>
//                array (
//                    'categories' =>
//                        Doctrine\ORM\Mapping\ManyToMany::__set_state(array(
//                            'targetEntity' => 'Webster\\Shop\\Entities\\Category',
//                            'mappedBy' => 'products',
//                            'inversedBy' => NULL,
//                            'cascade' => NULL,
//                            'fetch' => 'LAZY',
//                            'orphanRemoval' => false,
//                            'indexBy' => NULL,
//                        )),
//                ),
//            'parameters' =>
//                array (
//                ),
//            'reflClass' =>
//                ReflectionClass::__set_state(array(
//                    'name' => 'Webster\\Shop\\Entities\\Product',
//                )),
//            'reflFields' => NULL,
//            'identifier' => NULL,
//        ))


    }

    protected function _handleManyToOne(SearchManager $sm, $entity, ClassMetadata $md)
    {

    }

    protected function _handleOneToMany(SearchManager $sm, $entity, ClassMetadata $md)
    {

    }

    /**
     * Returns the read adapter
     *
     * @return null|ReadAdapter
     */
    public function getReadAdapter()
    {
        return $this->_readAdapter;
    }

    /**
     * Extracts the short class name of a long namespace name
     *
     * @param $name string The full namespace class name
     * @return string
     */
    protected function _className($name)
    {
        return end(explode('\\', $name));
    }

    protected function _read($object, $property)
    {
        $value = & \Closure::bind(function & () use ($property) {
            return $this->$property;
        }, $object, $object)->__invoke();
        return $value;
    }

    protected function _write($object, $property, $value)
    {
        $value = & \Closure::bind(function & () use ($property, $value) {
            $this->$property = $value;
            return $this->$property;
        }, $object, $object)->__invoke();
        return $value;
    }
}
