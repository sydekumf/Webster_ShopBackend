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
    const ELASTIC_INDEX = 'shop';

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
     * @var  $connectionParameters array
     */
    protected $connectionParameters;

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
    public function __construct(array $connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;
        $this->entityNamespace = array('namespace' => 'Entities', 'path' => '.');

        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            "/opt/appserver/webapps/shop/vendor/jms/serializer/src"
        );
        AnnotationRegistry::registerAutoloadNamespace(
            'Doctrine\Search\Mapping\Annotations',
            "/opt/appserver/webapps/shop/vendor/doctrine/search/lib"
        );

        $this->_setupDoctrine();
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
        return $this->connectionParameters;
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

    protected function _setUpDoctrine()
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
    }
}