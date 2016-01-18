<?php

namespace supervisormanager\tests\components\supervisor;

use supervisormanager\components\supervisor\Connection;
use Zend\Http\Client\Adapter\Exception\RuntimeException;
use Zend\XmlRpc\Client;
use Zend\XmlRpc\Client\Exception\FaultException;
use Zend\XmlRpc\Client\Exception\HttpException;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\XmlRpc\Client
     */
    protected $xmlRpcClient;

    public function setUp()
    {
        $httpClient = $this->getMockBuilder(\Zend\Http\Client::class)
            ->getMock();

        $httpClient->method('setAuth')->willReturn(true);

        $this->xmlRpcClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->xmlRpcClient->method('getHttpClient')->willReturn($httpClient);
    }

    public function testGetConnection()
    {
        $connection = new Connection($this->xmlRpcClient);

        $this->assertInstanceOf(Client::class, $connection->getConnection());
    }

    /**
     * @expectedException \supervisormanager\components\supervisor\exceptions\ConnectionException
     */
    public function testCallMethodThrowRuntimeException()
    {
        $this->xmlRpcClient->method('call')->willThrowException(
            new RuntimeException('message')
        );

        $connection = new Connection($this->xmlRpcClient);

        $connection->callMethod('test');
    }

    /**
     * @expectedException \supervisormanager\components\supervisor\exceptions\AuthenticationException
     */
    public function testCallMethodThrowHttpException()
    {
        $this->xmlRpcClient->method('call')->willThrowException(
            new HttpException('message')
        );

        $connection = new Connection($this->xmlRpcClient);

        $connection->callMethod('test');
    }

    /**
     * @expectedException \supervisormanager\components\supervisor\exceptions\SupervisorException
     * @expectedExceptionMessageRegExp #.*was not found.*#
     */
    public function testCallMethodThrowSupervisorException()
    {
        $this->xmlRpcClient->method('call')->willThrowException(
            new FaultException('message')
        );

        $connection = new Connection($this->xmlRpcClient);

        $connection->callMethod('test');
    }

    public function testCheckConnection()
    {
        $map = [
            ['a', 'b', 'c', 'd'],
            ['e', 'f', 'g', 'h']
        ];

        $this->xmlRpcClient->method('call')->willReturnMap($map);

        $connection = new Connection($this->xmlRpcClient);

        $this->assertInternalType('int', $connection->checkConnection());
    }

    public function tearDown()
    {
        unset($this->xmlRpcClient);
    }
}