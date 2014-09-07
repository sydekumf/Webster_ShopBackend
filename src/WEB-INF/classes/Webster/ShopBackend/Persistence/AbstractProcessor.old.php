<?php
/**
 * Abstract processor class
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

namespace Webster\Shop\Persistence;

use TechDivision\PersistenceContainerClient\Context\Connection\Factory;
use TechDivision\ApplicationServer\Interfaces\ApplicationInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Doctrine\Common\ClassLoader;
use Doctrine\Search\Configuration;
use Doctrine\Common\EventManager;
use Doctrine\Search\SearchManager;
use Elastica\Client;
use Doctrine\Search\ElasticSearch\Client as ElasticaAdapter;
use Doctrine\Search\Serializer\JMSSerializer;
use JMS\Serializer\SerializationContext;

/**
 * Webster\Shop\Services\AbstractProcessor
 *
 * Abstract processor class
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
class AbstractProcessor
{
    /**
     * Entity namespace
     *
     * @var array
     */
    protected $entityNamespace;

    /**
     * The Elastica client instance
     *
     * @var  $elastica \Elastica\Client
     */
    protected $elastica;

    /**
     * The doctrine search manager.
     *
     * @var  $sm Doctrine\Search\SearchManager
     */
    protected $sm;

    /**
     * @var  $readAdapter ReadAdapter
     */
    protected $readAdapter;

    /**
     * @var  $settings array
     */
    protected $settings;

    /**
     * Initializes the session bean with the Application instance.
     *
     * Checks on every start if the database already exists, if not
     * the database will be created immediately.
     *
     * @param Application $application
     *            The application instance
     *
     * @return void
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;

        $readAdapterClassName = $settings['adapter']['read'];
        $this->readAdapter = new $readAdapterClassName(
            $this->getElasticaClient(),
            $settings['elasticsearch']['index']
        );

        $this->entityNamespace = array('namespace' => 'Webster\\Shop\\Entities', 'path' => '.');

        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            "/opt/appserver/webapps/shop/vendor/jms/serializer/src"
        );
        AnnotationRegistry::registerAutoloadNamespace(
            'Doctrine\Search\Mapping\Annotations',
            "/opt/appserver/webapps/shop/vendor/doctrine/search/lib"
        );
        AnnotationRegistry::registerAutoloadNamespace(
            'Doctrine\ORM\Mapping',
            "/opt/appserver/app/code/vendor/doctrine/orm/lib"
        );

        $this->_setupDoctrine($settings['adapter']['relationHandler']);
    }

    /**
     * Validates an entity if it is safe for persistence according to its annotations.
     *
     * @param $entity mixed The entity to validate
     * @param $properties array The properties to validate
     * @throws \Exception
     */
    protected function validateEntity($entity, $properties = null)
    {
        if(!is_array($properties)){
            $violations = $this->getValidator()->validate($entity);
        } else {
            $violations = new ConstraintViolationList();
            foreach($properties as $property){
                $violation = $this->getValidator()->validateProperty($entity, $property);
                $violations->addAll($violation);
            }
        }

        // check for violations
        if (count($violations) > 0) {
            throw new \Exception('Entity ' . get_class($entity) . ' is not valid: ' . $violations->__toString());
        }
    }

    public function getValidator()
    {
        // register namespace mapping for symfony validator bundle
        AnnotationRegistry::registerAutoloadNamespace(
            'Symfony\Component\Validator\Constraints',
            $this->getApplication()->getWebappPath() . DIRECTORY_SEPARATOR . '../../app/code/vendor/symfony/symfony/src'
        );
        // get validator
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    }

    /**
     * Return's the entity namespaces
     *
     * @return array
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * Returns the database connection parameters used to connect to Doctrine.
     *
     * @return array The Doctrine database connection parameters
     */
    public function getConnectionParameters()
    {
        return array(
            'host' => $this->settings['elasticsearch']['host'],
            'port' => $this->settings['elasticsearch']['port']
        );
    }

    /**
     * Return's the initialized Elastica client instance
     *
     * @return \Elastica\Client The initialized Elastica client
     */
    public function getElasticaClient()
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        if(!$this->elastica){
            $this->elastica = new \Elastica\Client($this->getConnectionParameters());
        }
        return $this->elastica;
    }

    /**
     * Returns the doctrine search manager.
     *
     * @return Doctrine\Search\SearchManager
     */
    public function getSearchManager()
    {
        return $this->sm;
    }

    public function getReadAdapter()
    {
        return $this->readAdapter;
    }

    protected function _setUpDoctrine($relationHandlerClassName)
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        $entityNamespace = $this->getEntityNamespace();

        // set up entities
        $cl = new ClassLoader(
            $entityNamespace['namespace'],
            $entityNamespace['path']
        );
        $cl->register();

        //Annotation metadata driver
        $config = new Configuration();
        $md = $config->newDefaultAnnotationDriver(array($entityNamespace['namespace']));
        $config->setMetadataDriverImpl($md);

        // set and configure preferred serializer for persistence
        $config->setEntitySerializer(new JMSSerializer(
            SerializationContext::create()
        ));

        // get the search manager
        $this->sm = new SearchManager(
            $config,
            new ElasticaAdapter($this->getElasticaClient()),
            new EventManager()
        );

        if($relationHandlerClassName){
            $relationHandler = new $relationHandlerClassName($this->getReadAdapter());
            $this->sm->getEventManager()->addEventSubscriber($relationHandler);
        }
    }

//    /**
//     * Returns the elasticsearch index.
//     *
//     * @return \Elastica\Index
//     */
//    protected function getIndex()
//    {
//        $elastica = $this->getElasticaClient();
//        return $elastica->getIndex($this->_getIndexName());
//    }
//
//    /**
//     * Returns the elasticsearch type.
//     *
//     * @return \Elastica\Type
//     */
//    protected function getType()
//    {
//        if(!$this->_getTypeName()){
//            return null;
//        }
//        $index = $this->getIndex();
//        return $index->getType($this->_getTypeName());
//    }

    /**
     * Returns all found entities optionally filtered by an array of ids.
     *
     * @return array
     */
    public function findAll($ids = null)
    {
        return $this->getReadAdapter()
            ->findAll(
                $this->_getEntityClassName(),
                $this->_getTypeName(),
                $ids
            );
    }

    /**
     * Returns an entity by its id.
     *
     * @param $productId
     */
    public function findById($id)
    {
        return $this->getReadAdapter()
            ->findById(
                $this->_getEntityClassName(),
                $this->_getTypeName(),
                $id
            );
    }

    /**
     * Returns the full class name for the current processor's entity.
     *
     * @return string
     */
    protected function _getEntityClassName()
    {
        $en = $this->getEntityNamespace();
        return $en['namespace'] . '\\' . ucfirst($this->_getTypeName());
    }

    /**
     * Returns the type name as a string for the current processor's entity.
     *
     * @return string
     */
    protected function _getTypeName()
    {
        $processorName = explode('\\', get_class($this));
        $typeName = end($processorName);
        $typeName = substr($typeName, 0, strlen($typeName) - strlen('Processor'));
        return lcfirst($typeName);
    }

    protected function _getIndexName()
    {
        return $this->settings['elasticsearch']['index'];
    }
}