<?php

namespace supervisormanager\components\supervisor\control;

use supervisormanager\components\supervisor\Connection;
use supervisormanager\components\supervisor\control\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var Group
     */
    private $group;

    public function setUp()
    {
        $this->config = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new Group('test-process', $this->config);
    }

    public function testStartProcessGroup()
    {
        $this->config->method('callMethod')
            ->with(
                $this->equalTo('supervisor.startProcessGroup'),
                $this->equalTo(['test-process'])
            )->willReturn(true);

        $this->assertEquals(true, $this->group->startProcessGroup());
    }

    public function testStopProcessGroup()
    {
        $this->config->method('callMethod')
            ->with(
                $this->equalTo('supervisor.stopProcessGroup'),
                $this->equalTo(['test-process'])
            )->willReturn(true);

        $this->assertEquals(true, $this->group->stopProcessGroup());
    }
}