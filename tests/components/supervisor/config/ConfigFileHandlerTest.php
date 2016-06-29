<?php

namespace supervisormanager\components\supervisor\config;

use supervisormanager\components\supervisor\config\ConfigFileHandler;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class ConfigFileHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $configDir;

    /**
     * @var ConfigFileHandler
     */
    private $configHandler;

    /**
     * @var string
     */
    private $configDirPath;

    public function setUp()
    {
        $this->configDir = vfsStream::setup('root', 777, ['test' => []]);

        $this->configDirPath = $this->configDir->url()
            . DIRECTORY_SEPARATOR . 'test';

        $this->configHandler = new ConfigFileHandler(
            'test-process', $this->configDirPath
        );
    }

    public function testConstruct()
    {
        $this->assertFileExists(
            $this->configDir->url() . DIRECTORY_SEPARATOR . 'test'
        );

        unset($configHandler);

        new ConfigFileHandler('test-process', $this->configDirPath);

        $this->assertEquals(
            ['root' => ['test' => []]],
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );
    }

    /**
     * @depends testConstruct
     */
    public function testBackupConfig()
    {
        $testFolderPath = __DIR__ . '/test-folder';

        $configHandler = new ConfigFileHandler(
            'test-process', $testFolderPath
        );

        chmod($testFolderPath, 0777);

        file_put_contents(
            $testFolderPath . '/test.conf', "The new contents of the file"
        );

        $configHandler->backupConfig('test.zip');

        $this->assertFileExists(
            $testFolderPath . '/test.zip'
        );

        unlink($testFolderPath . '/test.conf');

        unlink($testFolderPath . '/test.zip');

        rmdir($testFolderPath);
    }

    /**
     * @depends testBackupConfig
     */
    public function testRestoreFromBackup()
    {
        $testFolderPath = __DIR__ . '/test-folder';

        $configHandler = new ConfigFileHandler(
            'test-process', $testFolderPath
        );

        chmod($testFolderPath, 0777);

        file_put_contents(
            $testFolderPath . '/test.conf', "The new contents of the file"
        );

        $configHandler->backupConfig('test.zip');

        $this->assertFileExists(
            $testFolderPath . '/test.zip'
        );

        $configHandler->restoreFromBackup('test.zip');

        $this->assertFileExists(
            $testFolderPath . '/test.conf'
        );

        unlink($testFolderPath . '/test.conf');

        unlink($testFolderPath . '/test.zip');

        rmdir($testFolderPath);
    }

    public function testCreateConfig()
    {
        $configData = "command=task-runner";

        $this->configHandler->createConfig('test-process', $configData);

        $this->assertFileExists(
            $this->configDirPath . DIRECTORY_SEPARATOR . 'test-process.conf'
        );
    }

    /**
     * @depends testCreateConfig
     */
    public function testGetProcessConfig()
    {
        $configData = "command=task-runner";

        $this->configHandler->createConfig('test-process', $configData);

        $this->assertFileExists(
            $this->configDirPath . DIRECTORY_SEPARATOR . 'test-process.conf'
        );

        $this->assertEquals(
            'command=task-runner', $this->configHandler->getProcessConfig()
        );
    }

    /**
     * @depends testGetProcessConfig
     */
    public function testDeleteGroup()
    {
        $configData = "command=task-runner";

        $this->configHandler->createConfig('test-process', $configData);

        $this->configHandler->getProcessConfig();

        $this->assertFileExists(
            $this->configDirPath . DIRECTORY_SEPARATOR . 'test-process.conf'
        );

        $this->configHandler->deleteGroup();

        $this->assertFileNotExists(
            $this->configDirPath . DIRECTORY_SEPARATOR . 'test-process.conf'
        );
    }

    /**
     * @depends testGetProcessConfig
     */
    public function testSaveConfig()
    {
        $configData = "command=task-runner";

        $this->configHandler->createConfig('test-process', $configData);

        $this->assertFileExists(
            $this->configDirPath . DIRECTORY_SEPARATOR . 'test-process.conf'
        );

        $this->configHandler->getProcessConfig();

        $this->configHandler->saveConfig('user=test');

        $this->assertEquals(
            'user=test', $this->configHandler->getProcessConfig()
        );
    }
}