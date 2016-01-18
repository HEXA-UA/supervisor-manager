<?php

namespace supervisormanager\components\supervisor\control;

use supervisormanager\components\supervisor\Connection;
use supervisormanager\components\supervisor\control\Process;
use org\bovigo\vfs\vfsStream;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var Process
     */
    private $process;

    public function setUp()
    {
        $this->config = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->process = new Process('test-process', $this->config);
    }

    public function testStopProcess()
    {
        $this->config->method('callMethod')
            ->with(
                $this->equalTo('supervisor.stopProcess'),
                $this->equalTo(['test-process'])
            )->willReturn(true);

        $this->assertEquals(true, $this->process->stopProcess());
    }

    public function testStartProcess()
    {
        $this->config->method('callMethod')
            ->with(
                $this->equalTo('supervisor.startProcess'),
                $this->equalTo(['test-process'])
            )->willReturn(true);

        $this->assertEquals(true, $this->process->startProcess());
    }

    public function testGetProcessInfo()
    {
        $processConfig = [
            'description' => 'pid 8847, uptime 0:28:32',
            'pid' => 8847,
            'group' => 'test-process',
            'name' => 'test-process_00',
            'priority' => 111
        ];

        $this->config->method('callMethod')
            ->with(
                $this->equalTo('supervisor.getProcessInfo'),
                $this->equalTo(['test-process'])
            )->willReturn($processConfig);

        $this->assertEquals($processConfig, $this->process->getProcessInfo());
    }

    /**
     * @expectedException \supervisormanager\components\supervisor\exceptions\ProcessException
     */
    public function testGetProcessOutput()
    {
        vfsStream::setup(
            'root',
            null,
            [
                'logErrorFile.txt' => 'Test error log output',
                'logOutputFile.txt' => 'Test log output',
            ]
        );

        $processConfig = [
            'stderr_logfile' => vfsStream::url('root') . '/logErrorFile.txt',
            'stdout_logfile' => vfsStream::url('root') . '/logOutputFile.txt'
        ];

        $this->config->method('callMethod')
            ->with(
                $this->equalTo('supervisor.getProcessInfo'),
                $this->equalTo(['test-process'])
            )->willReturn($processConfig);

        $this->assertEquals(
            'Test error log output',
            $this->process->getProcessOutput('stderr_logfile')
        );

        $this->assertEquals(
            'Test log output',
            $this->process->getProcessOutput('stdout_logfile')
        );

        $this->process->getProcessOutput('stdout');
    }
}