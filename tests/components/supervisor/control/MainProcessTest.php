<?php

namespace supervisormanager\components\supervisor\control;

use supervisormanager\components\supervisor\Connection;
use supervisormanager\components\supervisor\control\MainProcess;

class MainProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var MainProcess
     */
    private $mainProcess;

    public function setUp()
    {
        $this->config = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mainProcess = new MainProcess($this->config);
    }

    public function testGetAllProcessesByGroup()
    {
        $processConfig = [[
            'description' => 'pid 8847, uptime 0:28:32',
            'pid' => 8847,
            'group' => 'test-process',
            'name' => 'test-process_00',
            'priority' => 111
        ]];

        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.getAllProcessInfo'))
            ->willReturn($processConfig);

        $processByGroup = $this->mainProcess->getAllProcessesByGroup();

        $this->assertArrayHasKey('test-process', $processByGroup);

        $this->assertArrayHasKey('processList', $processByGroup['test-process']);

        $this->assertArrayHasKey(
            'description', $processByGroup['test-process']['processList'][0]
        );
    }

    public function testGetAPIVersion()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.getAPIVersion'))
            ->willReturn('1.0');

        $this->assertEquals('1.0', $this->mainProcess->getAPIVersion());
    }

    public function testGetState()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.getState'))
            ->willReturn('test-state');

        $this->assertEquals('test-state', $this->mainProcess->getState());
    }

    public function testGetProcessId()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.getPID'))
            ->willReturn(1111);

        $this->assertEquals(1111, $this->mainProcess->getProcessId());
    }

    public function testRestart()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.restart'))
            ->willReturn(true);

        $this->assertEquals(true, $this->mainProcess->restart());
    }

    public function testStart()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.startAllProcesses'))
            ->willReturn(true);

        $this->assertEquals(true, $this->mainProcess->start());
    }

    public function testForceStart()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.startAllProcesses'))
            ->willReturn(true);

        $this->assertEquals(true, $this->mainProcess->start());
    }

    public function testShutdown()
    {
        $this->config->method('callMethod')
            ->with($this->equalTo('supervisor.shutdown'))
            ->willReturn(true);

        $this->assertEquals(true, $this->mainProcess->shutdown());
    }
}