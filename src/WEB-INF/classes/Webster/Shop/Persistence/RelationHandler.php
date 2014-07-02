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

        foreach($md['fieldMappings'] as $thisAttributeName => $data){
            $otherAttributeName = null;
            if(isset($data->mappedBy)){
                $otherAttributeName = $data->mappedBy;
            } else if (isset($data->inversedBy)){
                $otherAttributeName = $data->inversedBy;
            }

            $targetEntity = $data->targetEntity;
            $oldIds = $oldEntity->{'get' . ucfirst($thisAttributeName)}();
            $newIds = $entity->{'get' . ucfirst($thisAttributeName)}();

            $deleteIds = array_diff($oldIds, $newIds);
            $newIds = array_diff($newIds, $oldIds);

            $type = $sm->getClassMetadata(get_class($entity))->type;

            $className = $this->_className($md->className);

            foreach($this->getReadAdapter()->findAll($targetEntity, $type, $deleteIds) as $deleteEntity){
                $deleteEntity->{'remove' . $className}($entity);
                $sm->persist($deleteEntity);
            }

            foreach($this->getReadAdapter()->findAll($targetEntity, $type, $newIds) as $newEntity){
                $newEntity->{'add' . $this->_className($className)}($entity);
                $sm->persist($newEntity);
            }
        }


//        $oldProduct = $this->findById($product->getId());
//        $oldCategories = $oldProduct->getCategories();
//        $categories = $product->getCategories();
//
//        $deleteCategoryIds = array_diff($oldCategories, $categories);
//        $newCategoryIds = array_diff($categories, $oldCategories);
//
//        $categoryProcessor = new CategoryProcessor($this->getConnectionParameters());
//
//        foreach($categoryProcessor->findAll($deleteCategoryIds) as $category){
//            $category->removeProduct($product);
//            $categoryProcessor->persist($category);
//        }
//
//        foreach($categoryProcessor->findAll($newCategoryIds) as $category){
//            $category->addProduct($product);
//            $categoryProcessor->persist($category);
//        }




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
        return end(explode('\\', get_class($name)));
    }
}
