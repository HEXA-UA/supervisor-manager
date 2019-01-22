<?php

namespace supervisormanager\components\supervisor\config;

use yii\base\Component;

/**
 * Class ConfigFileHandler
 *
 * @package supervisormanager\components\supervisor\config
 */
class ConfigFileHandler extends Component
{
    const BACKUP_FILE_NAME = 'config_backup.zip';

    const DS = DIRECTORY_SEPARATOR;

    /**
     * @var string Path to supervisor configuration dir
     */
    private $_configDir;

    /**
     * @var string Current group name working with
     */
    private $_processName;

    /**
     * @var string Path to process configuration file
     */
    private $_configPath;

    /**
     * @var string Source string with supervisor configuration for specified
     * group.
     */
    private $_configSource;

    /**
     * ConfigFileHandler constructor.
     *
     * @param string $processName
     * @param string $configDir
     * @param array  $config
     */
    public function __construct(
        $processName = null, $configDir = null, $config = []
    ) {
        $this->_processName = $processName;

        $this->_setConfigDir($configDir);

        $this->_checkConfigDir();

        parent::__construct($config);
    }

    /**
     * Check supervisor configurations folder for existing and creates if it's
     * not.
     *
     * @return bool
     */
    private function _checkConfigDir()
    {
        if (!is_dir($this->_configDir)) {
            return mkdir($this->_configDir, 0777);
        }

        return true;
    }

    /**
     * @param $configDir
     */
    private function _setConfigDir($configDir)
    {
        if (!$configDir) {
            $this->_configDir = \Yii::getAlias('@common') . '/config/supervisor';

            return;
        }

        $this->_configDir = $configDir;
    }

    /**
     * Get a list of paths to all configuration files of supervisor.
     *
     * @return array
     */
    private function _getConfigFilesPaths()
    {
        $filesFilterCallback = function ($item) {
            return !is_dir($this->_configDir . self::DS . $item)
            && !strpos($item, '.zip');
        };

        return array_filter(scandir($this->_configDir), $filesFilterCallback);
    }

    /**
     * Create backup of existing supervisor processes configuration files.
     *
     * @param string $backupName
     *
     * @return bool
     */
    public function backupConfig($backupName = null)
    {
        $zip = new \ZipArchive();

        $archiveName = $backupName ?: self::BACKUP_FILE_NAME;

        $archivePath = $this->_configDir . self::DS . $archiveName;

        // Create an archive
        if (!$zip->open($archivePath, \ZipArchive::CREATE)) {
            return false;
        }

        // Add files to archive
        $filesList = $this->_getConfigFilesPaths();

        foreach ($filesList as $filePath) {
            $zip->addFile(
                $this->_configDir . self::DS . $filePath, $filePath
            );
        }

        // Save archive
        return $zip->close();
    }

    /**
     * Restore previous configuration from archive.
     *
     * @param string $backupName
     *
     * @return bool
     */
    public function restoreFromBackup($backupName = null)
    {
        $zip = new \ZipArchive;

        $archiveName = $backupName ?: self::BACKUP_FILE_NAME;

        $archivePath = $this->_configDir . self::DS . $archiveName;

        if (!$zip->open($archivePath)) {
            return false;
        }

        $currentConfigFiles = $this->_getConfigFilesPaths();

        foreach ($currentConfigFiles as $filePath) {

            if (!strpos($filePath, 'zip')) {
                unlink($this->_configDir . self::DS . $filePath);
            }
        }

        $zip->extractTo($this->_configDir);

        return $zip->close();
    }

    /**
     * Update process configuration.
     *
     * @param $processData
     *
     * @return bool
     */
    public function saveConfig($processData)
    {
        if (!$this->_processName) {
            return false;
        }

        // Replace existing config with new
        $replacementCallback = function ($matches) use ($processData) {
            return $matches[1] . "\n" . $processData . "\n" . $matches[3];
        };

        $configString = preg_replace_callback(
            '/(\[.*:' . $this->_processName . '\])([\s-\S]+?)(\Z|\[)/',
            $replacementCallback,
            $this->_configSource
        );

        return $this->_saveFileConfig($configString);
    }

    /**
     * Save new process configuration to supervisor configuration file.
     *
     * @param $groupName
     * @param $processData
     *
     * @return int
     */
    public function createConfig($groupName, $processData)
    {
        $this->backupConfig();

        $processData = "[program:$groupName]" . "\n" . $processData;

        $fileName = $groupName . '.conf';

        return file_put_contents(
            $this->_configDir . DIRECTORY_SEPARATOR . $fileName, $processData
        );
    }

    /**
     * Delete supervisor group configuration.
     *
     * @param bool $backup
     *
     * @return bool
     */
    public function deleteGroup($backup = false)
    {
        if ($backup) {
            $this->backupConfig();
        }

        $configString = preg_replace_callback(
            '/(\[.*:' . $this->_processName . '\])([\s-\S]+?)(\Z|\[)/',
            function($matches) {
                return '' . $matches[3];
            },
            $this->_configSource
        );

        return $this->_saveFileConfig($configString);
    }

    /**
     * @param $configData
     *
     * @return bool
     */
    private function _saveFileConfig($configData)
    {
        if (!$this->_configPath) {
            return false;
        }

        if (!$configData) {
            unlink($this->_configPath);
        } else {
            file_put_contents($this->_configPath, $configData);
        }

        return true;
    }

    /**
     * @param null $processName
     *
     * @return bool|string
     */
    public function getProcessConfig($processName = null)
    {
        if (!$this->_processName) {
            $this->_processName = $processName;
        }

        if (!$this->_processName) {
            return false;
        }

        $filesList = $this->_getConfigFilesPaths();

        // Search specified group config in each config file
        foreach ($filesList as $fileConfig) {

            $configPath = $this->_configDir . '/' . $fileConfig;

            $configData = file_get_contents($configPath);

            if (!strpos($configData, ":$this->_processName]")) {
                continue;
            }

            $this->_configPath = $configPath;

            $this->_configSource = $configData;

            // Get existing process configuration
            preg_match(
                '/\[.*:' . $this->_processName . '\]([\s-\S]+?)(\Z|\[)/',
                $configData,
                $result
            );

            if (!isset($result[1])) {
                return false;
            }

            return trim($result[1]);
        }

        return false;
    }


}
