<?php

use Webster\ShopBackend\Handler\Dispatcher;

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
class DispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject $_configMock Mock object for configuration
     */
    private $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject $_processorFactoryMock Mock object for processor factory
     */
    private $_processorFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject $_connectionInterface Mock object for connection interface
     */
    private $_connectionInterface;

    /**
     * @var Webster\ShopBackend\Handler\Dispatcher $_dispatcher Instance to test
     */
    private $_dispatcher;

    public function setUp()
    {
        $this->_configMock = $this->getMockBuilder('Noodlehaus\Config')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();
        $this->_configMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue('Webster\ShopBackend'));

        $this->_processorFactoryMock = $this->getMockBuilder('Webster\ShopBackend\Persistence\ProcessorFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_connectionInterface = $this->getMockBuilder('Ratchet\ConnectionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_dispatcher = new Dispatcher($this->_configMock, $this->_processorFactoryMock);
    }

    public function testDispatchMessage_emptyString()
    {
        $result = $this->_dispatcher->dispatchMessage('', $this->_connectionInterface);
        $this->assertFalse($result);
    }

    public function testDispatchMessage_whitespaces()
    {
        $result = $this->_dispatcher->dispatchMessage(' ', $this->_connectionInterface);
        $this->assertFalse($result);
    }

    /**
     * @expectedException Exception
     */
    public function testDispatchMessage_wrongJson()
    {
        $this->_dispatcher->dispatchMessage('{a:b}', $this->_connectionInterface);
    }

    /**
     * @expectedException Exception
     */
    public function testDispatchMessage_wrongMessageMissingType()
    {
        $this->_dispatcher->dispatchMessage('{"action":"test"}', $this->_connectionInterface);
    }

    /**
     * @expectedException Exception
     */
    public function testDispatchMessage_wrongMessageMissingAction()
    {
        $this->_dispatcher->dispatchMessage('{"type":"test"}', $this->_connectionInterface);
    }

    /**
     * @expectedException Exception
     */
    public function testDispatchMessage_wrongMessageMissingTypeAndAction()
    {
        $this->_dispatcher->dispatchMessage('{}', $this->_connectionInterface);
    }

    /**
     * @expectedException Exception
     */
    public function testDispatchMessage_controllerClassNotFound()
    {
        $this->_dispatcher->dispatchMessage('{"type":"not/found", "action":"test"}', $this->_connectionInterface);
    }

    /**
     * @expectedException Exception
     */
    public function testDispatchMessage_actionFunctionNotFound()
    {
        $this->_dispatcher->dispatchMessage('{"type":"catalog/category", "action":"test"}', $this->_connectionInterface);
    }

    public function testGetClassInfo_invalidClassId()
    {
        $result = $this->_dispatcher->getClassInfo('');
        $this->assertEmpty($result);
    }

    public function testGetClassInfo_slashOnly()
    {
        $expectedResult = array();

        $result = $this->_dispatcher->getClassInfo('/');
        $this->assertEquals($result, $expectedResult);
    }

    public function testGetClassInfo_missingType()
    {
        $expectedResult = array(
            'module' => 'Catalog',
            'type'   => null
        );

        $result = $this->_dispatcher->getClassInfo('catalog/');
        $this->assertEquals($result, $expectedResult);
    }

    public function testGetClassInfo_upperCaseClassId()
    {
        $expectedResult = array(
            'module' => 'Catalog',
            'type'   => 'Product'
        );

        $result = $this->_dispatcher->getClassInfo('Catalog/Product');
        $this->assertEquals($result, $expectedResult);
    }

    public function testGetClassInfo()
    {
        $expectedResult = array(
            'module' => 'Category',
            'type'   => 'Product\View'
        );

        $result = $this->_dispatcher->getClassInfo('category/product_view');
        $this->assertEquals($result, $expectedResult);
    }

    public function testUcwords()
    {
        $expectedResult = 'Catalog\Product';

        $result = $this->_dispatcher->ucwords('catalog_product', '\\');
        $this->assertEquals($result, $expectedResult);
    }
}