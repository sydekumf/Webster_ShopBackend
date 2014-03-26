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

namespace Webster\Shop\Services;

use TechDivision\PersistenceContainerClient\Context\Connection\Factory;
use TechDivision\ApplicationServer\Interfaces\ApplicationInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

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
     * Datasource name to use.
     *
     * @var string
     */
    protected $datasourceName = 'Webster';

    /**
     * Entity namespace
     *
     * @var array
     */
    protected $entityNamespaces;

    /**
     * The application instance that provides the entity manager.
     *
     * @var Application
     */
    protected $application;

    /**
     * The Elastica client instance
     *
     * @var  $elastica \Elastica\Client
     */
    protected $elastica;

    /**
     * @var  $connection Holds the connection of the persistence container socket
     */
    protected $persistenceConnection;

    /**
     * @var  $session Holds the persistence container socket session
     */
    protected $session;

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
    public function __construct(ApplicationInterface $application)
    {
        $this->entityNamespaces = array(
            'Webster\\Shop\\Entities\\Product'
        );

        // set the application instance and initialize the connection parameters
        $this->setApplication($application);
        $this->initConnectionParameters();

        $this->persistenceConnection = Factory::createContextConnection('shop');
        $this->session = $this->persistenceConnection->createContextSession();

        $this->createIndex();
    }

    /**
     * Returns a proxy class for a given class name.
     *
     * @param $class The class name
     * @return mixed
     */
    public function getProxy($class)
    {
        $initialContext = $this->session->createInitialContext();
        return $initialContext->lookup($class);
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
    public function getEntityNamespaces()
    {
        return $this->entityNamespaces;
    }

    /**
     * Return's the datasource name to use.
     *
     * @return string The datasource name
     */
    public function getDatasourceName()
    {
        return $this->datasourceName;
    }

    /**
     * The application instance providing the database connection.
     *
     * @param
     *            \TechDivision\ApplicationServer\Interfaces\ApplicationInterface The application instance
     *
     * @return void
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * The application instance providing the database connection.
     *
     * @return Application The application instance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * The database connection parameters used to connect to Doctrine.
     *
     * @param array $connectionParameters
     *            The Doctrine database connection parameters
     *
     * @return
     *
     */
    public function setConnectionParameters(array $connectionParameters = array())
    {
        $this->connectionParameters = $connectionParameters;
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
     * Return's the initial context instance.
     *
     * @return \TechDivision\ApplicationServer\InitialContext The initial context instance
     */
    public function getInitialContext()
    {
        return $this->getApplication()->getInitialContext();
    }

    /**
     * Return's the system configuration
     *
     * @return \TechDivision\ApplicationServer\Api\Node\NodeInterface The system configuration
     */
    public function getSystemConfiguration()
    {
        return $this->getInitialContext()->getSystemConfiguration();
    }

    /**
     * Return's the array with the datasources.
     *
     * @return array The array with the datasources
     */
    public function getDatasources()
    {
        return $this->getSystemConfiguration()->getDatasources();
    }

    /**
     * Return's the initialized Elastica client instance
     *
     * @return \Elastica\Client The initialized Elastica client
     */
    public function getElasticaClient()
    {
        require_once '/opt/appserver/webapps/shop/vendor/autoload.php';

        return new \Elastica\Client($this->getConnectionParameters());
    }

    /**
     * Initializes the database connection parameters necessary
     * to connect to the database using Elastica.
     *
     * @return void
     */
    public function initConnectionParameters()
    {
        // iterate over the found database sources
        foreach ($this->getDatasources() as $datasourceNode) {

            // if the datasource is related to the session bean
            if ($datasourceNode->getName() == $this->getDatasourceName()) {

                // initialize the database node
                $databaseNode = $datasourceNode->getDatabase();

                // initialize the connection parameters
                $connectionParameters = array(
                    'host' => $databaseNode->getUser()
                            ->getNodeValue()
                            ->__toString(),
                    'port' => (int) $databaseNode->getPassword()
                            ->getNodeValue()
                            ->__toString()
                );

                // set the connection parameters
                $this->setConnectionParameters($connectionParameters);
            }
        }
    }

    /**
     * Builds up the elastic search index and all entities' mappings
     *
     * @return void
     */
    public function createIndex()
    {
        // Load index
        $elasticaIndex = $this->getElasticaClient()->getIndex(self::ELASTIC_INDEX);

        if($elasticaIndex->exists()) return;

        // Create new index
        $elasticaIndex->create(
            array(
                'number_of_shards' => 4,
                'number_of_replicas' => 1,
                'analysis' => array(
                    'analyzer' => array(
                        'indexAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'keyword'
                        ),
                        'searchAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'keyword'
                        )
                    )
                )
            )
        );

        foreach($this->getEntityNamespaces() as $entity){
            call_user_func(array($entity, 'createMapping'), $elasticaIndex);
        }
    }
}