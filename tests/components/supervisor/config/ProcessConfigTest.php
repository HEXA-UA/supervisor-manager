<?php

namespace supervisormanager\components\supervisor\config;

use supervisormanager\components\supervisor\config\ConfigFileHandler;
use supervisormanager\components\supervisor\config\ProcessConfig;

class ProcessConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $processConfig;

    /**
     * @var ConfigFileHandler | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configHandler;

    /**
     * @var string
     */
    private $testProcessConfig;

    public function setUp()
    {
        $this->configHandler = $this->getMockBuilder(
            ConfigFileHandler::class
        )->disableOriginalConstructor()->getMock();

        $this->configHandler->method('createConfig')
            ->willReturn(true);

        $this->processConfig = $this
            ->getMockBuilder(ProcessConfig::className())
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->testProcessConfig
            = "command=test-command
                process_name=%(program_name)s_%(process_num)02d
                numprocs=2
                numprocs_start=0
                priority=999
                autostart=1
                startsecs=2
                startretries=4
                autorestart=unexpected
                exitcodes=2
                stopsignal=TERM
                stopwaitsecs=15";

        $this->processConfig->setConfigHandler($this->configHandler);
    }

    public function testNewConfigCreation()
    {
        $this->configHandler->method('getProcessConfig')->willReturn(false);

        $this->processConfig->prepareProcessConfig();

        $this->assertEquals('create', $this->processConfig->getState());
    }

    public function additionProvider()
    {
        return [[1], [2], [3], [4]];
    }

    public function termsValuesProvider()
    {
        return [
            ['TERM'], ['HUP'], ['INT'], ['QUIT'], ['KILL'], ['USR1'], ['USR2']
        ];
    }

    public function autoRestartValuesProvider()
    {
        return [['false'], ['unexpected'], ['true']];
    }

    /**
     * @dataProvider additionProvider
     */
    public function testSetStartsecs($startsecs)
    {
        $this->processConfig->setStartsecs($startsecs);

        $this->assertEquals(
            $startsecs, $this->processConfig->getStartsecs()
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testSetStopwaitsecs($stopwaitsecs)
    {
        $this->processConfig->setStopwaitsecs($stopwaitsecs);

        $this->assertEquals(
            $stopwaitsecs, $this->processConfig->getStopwaitsecs()
        );
    }

    /**
     * @dataProvider termsValuesProvider
     * @expectedException \supervisormanager\components\supervisor\exceptions\ProcessConfigException
     */
    public function testSetStopsignal($stopsignal)
    {
        $this->processConfig->setStopsignal($stopsignal);

        $this->assertEquals(
            $stopsignal, $this->processConfig->getStopsignal()
        );

        $this->processConfig->setStopsignal(strrev($stopsignal));

        $this->assertEquals(
            $stopsignal, $this->processConfig->getStopsignal()
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testSetExitcodes($exitcodes)
    {
        $this->processConfig->setExitcodes($exitcodes);

        $this->assertEquals(
            $exitcodes, $this->processConfig->getExitcodes()
        );
    }

    /**
     * @dataProvider autoRestartValuesProvider
     * @expectedException \supervisormanager\components\supervisor\exceptions\ProcessConfigException
     */
    public function testSetAutorestart($autorestart)
    {
        $this->processConfig->setAutorestart($autorestart);

        $this->assertEquals(
            $autorestart, $this->processConfig->getAutorestart()
        );

        $this->processConfig->setAutorestart(strrev($autorestart));

        $this->assertEquals(
            $autorestart, $this->processConfig->getAutorestart()
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testSetStartretries($startretries)
    {
        $this->processConfig->setStartretries($startretries);

        $this->assertEquals(
            $startretries, $this->processConfig->getStartretries()
        );
    }

    public function testSetAutostart()
    {
        $this->processConfig->setAutostart('true');

        $this->assertInternalType('int', $this->processConfig->getAutostart());
    }

    /**
     * @expectedException \supervisormanager\components\supervisor\exceptions\ProcessConfigException
     */
    public function testSetPriority()
    {
        $this->processConfig->setPriority(100);

        $this->assertInternalType('int', $this->processConfig->getPriority());

        $this->assertEquals(100, $this->processConfig->getPriority());

        $this->processConfig->setPriority(1000);
    }

    public function testSetCommand()
    {
        $this->processConfig->setCommand('test-command');

        $this->assertEquals(
            'test-command', $this->processConfig->getCommand()
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testSetNumprocs($numprocs)
    {
        $this->processConfig->setNumprocs($numprocs);

        $this->assertEquals(
            $numprocs, $this->processConfig->getNumprocs()
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testSetNumprocs_start($numprocs)
    {
        $this->processConfig->setNumprocs_start($numprocs);

        $this->assertEquals(
            $numprocs, $this->processConfig->getNumprocs_start()
        );
    }

    public function testSetProcess_name()
    {
        $this->processConfig->setProcess_name('test-process');

        $this->assertEquals(
            'test-process', $this->processConfig->getProcess_name()
        );
    }

    public function testPrepareProcessConfig()
    {
        $this->configHandler->method('getProcessConfig')
            ->willReturn($this->testProcessConfig);

        $this->processConfig->prepareProcessConfig();

        $processParamsArray = explode("\n", $this->testProcessConfig);

        foreach ($processParamsArray as $configParam) {

            list($paramName, $paramValue) = explode("=", $configParam);

            $this->assertEquals(
                $paramValue,
                call_user_func(
                    [$this->processConfig, 'get' . trim(ucfirst($paramName))]
                )
            );
        }
    }

    public function testSaveProcessConfigUpdateState()
    {
        $this->configHandler->method('getProcessConfig')
            ->willReturn($this->testProcessConfig);

        $this->processConfig->prepareProcessConfig();

        $this->processConfig->saveProcessConfig();
    }

    public function testSaveProcessConfigCreateState()
    {
        $this->configHandler->method('saveConfig')->willReturn(true);

        $processParamsArray = explode("\n", $this->testProcessConfig);

        foreach ($processParamsArray as $configParam) {

            list($paramName, $paramValue) = explode("=", $configParam);

            call_user_func_array(
                [$this->processConfig, 'set' . trim(ucfirst($paramName))],
                [$paramValue]
            );
        }

//        $this->processConfig->prepareProcessConfig();

        $this->processConfig->saveProcessConfig();
    }

    /**
     * @depends testSaveProcessConfigUpdateState
     * @depends testSaveProcessConfigCreateState
     */
    public function testDeleteGroupProcess()
    {
        $this->configHandler->method('getProcessConfig')
            ->willReturn($this->testProcessConfig);

        $this->configHandler->method('saveConfig')->willReturn(true);

        $this->processConfig->prepareProcessConfig();

        $this->assertEquals(
            $this->processConfig->getNumprocs() - 1,
            $this->processConfig->deleteGroupProcess()
        );
    }

    public function testAddNewGroupProcess()
    {
        $this->configHandler->method('getProcessConfig')
            ->willReturn($this->testProcessConfig);

        $this->configHandler->method('saveConfig')->willReturn(true);

        $this->processConfig->prepareProcessConfig();

        $this->assertEquals(
            $this->processConfig->getNumprocs() + 1,
            $this->processConfig->addNewGroupProcess()
        );
    }

    /**
     * @depends testSaveProcessConfigUpdateState
     * @depends testSaveProcessConfigCreateState
     */
    public function testCreateGroup()
    {
        $processData = [
            'command' => 'test-command',
            'process_name' => 'process_name',
            'numprocs' => 1
        ];

        $this->processConfig->createGroup($processData);

        $this->assertEquals(
            $processData['command'], $this->processConfig->getCommand()
        );

        $this->assertEquals(
            $processData['process_name'], $this->processConfig->getProcess_name()
        );

        $this->assertEquals(
            $processData['numprocs'], $this->processConfig->getNumprocs()
        );
    }
}