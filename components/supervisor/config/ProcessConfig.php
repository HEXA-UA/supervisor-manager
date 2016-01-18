<?php

namespace supervisormanager\components\supervisor\config;

use supervisormanager\components\supervisor\exceptions\ProcessConfigException;
use yii\base\Component;

/**
 * Class ProcessConfig
 *
 * @package supervisormanager\components\supervisor\config
 */
class ProcessConfig extends Component
{
    /**
     * @var ConfigFileHandler
     */
    private $_config;

    /**
     * The command that will be run when this program is started. Example of
     * command value: "/path/to/program foo bar". The command line can use
     * double quotes to group arguments with spaces in them to pass to the
     * program, e.g. /path/to/program/name -p "foo bar".
     *
     * @var string
     */
    private $_command;

    /**
     * You don’t need this setting unless you change "numprocs" option.
     * Default: %(program_name)s
     *
     * @var string
     */
    private $_process_name = '%(program_name)s_%(process_num)02d';

    /**
     * The number of process instances that will be launched.
     * Default: 1
     *
     * @var int
     */
    private $_numprocs = 1;

    /**
     * An integer offset that is used to compute the number at which
     * numberOfProcesses starts.
     * Default: 0
     *
     * @var int
     */
    private $_numprocs_start = 0;

    /**
     * The relative priority of the program in the start and shutdown ordering.
     * Lower priorities indicate programs that start first and shut down last.
     * Default: 999
     *
     * @var int
     */
    private $_priority = 999;

    /**
     * If true, this program will start automatically when supervisor is started.
     * Default: true
     *
     * @var bool
     */
    private $_autostart = true;

    /**
     * The number of serial failure attempts that supervisor will allow when
     * attempting to start the program before giving up and putting the process
     * into an FATAL state.
     * Default: 3
     *
     * @var int
     */
    private $_startretries = 3;

    /**
     * Specifies if supervisor should automatically restart a process if it
     * exits when it is in the RUNNING state.
     * Available values: [false, unexpected, true].
     * Default: unexpected
     *
     * @var string
     */
    private $_autorestart = 'unexpected';

    /**
     * The list of “expected” exit codes for this program used with autoRestart.
     * Default: 0,2
     *
     * @var string
     */
    private $_exitcodes = '0,2';

    /**
     * The signal used to kill the program when a stop is requested.
     * Available values [TERM, HUP, INT, QUIT, KILL, USR1, USR2].
     * Default: TERM
     *
     * @var string
     */
    private $_stopsignal = 'TERM';

    /**
     * The number of seconds to wait for the OS to return a SIGCHILD to
     * supervisor after the program has been sent a stopsignal.
     * Default: 10
     *
     * @var int
     */
    private $_stopwaitsecs = 10;

    /**
     * The total number of seconds which the program needs to stay running after
     * a startup to consider the start successful. Set to 0 to indicate that the
     * program needn’t stay running for any particular amount of time.
     * Default: 1
     *
     * @var int
     */
    private $_startsecs = 1;

    /**
     * @var string
     */
    private $_programName;

    /**
     * @var string
     */
    private $_state = 'update';

    /**
     * @var array
     */
    private $_allowedConfigOptions
        = [
            'command',
            'process_name',
            'numprocs',
            'numprocs_start',
            'priority',
            'autostart',
            'startsecs',
            'startretries',
            'autorestart',
            'exitcodes',
            'stopsignal',
            'stopwaitsecs',
        ];

    /**
     * ProcessConfig constructor.
     *
     * @param array $programName
     * @param array $config
     * @codeCoverageIgnore
     */
    public function __construct($programName, $config = [])
    {
        $this->_programName = $programName;

        $this->_config = new ConfigFileHandler($this->_programName);

        $this->prepareProcessConfig();

        parent::__construct($config);
    }

    /**
     * @param ConfigFileHandler $configFileHandler
     */
    public function setConfigHandler(ConfigFileHandler $configFileHandler)
    {
        $this->_config = $configFileHandler;
    }

    /**
     * Sets the property values obtained from a configuration file.
     *
     * @throws ProcessConfigException
     * @throws \Exception
     */
    public function prepareProcessConfig()
    {
        $processConfigData = $this->_config->getProcessConfig(
            $this->_programName
        );

        if (!$processConfigData) {
            $this->_state = 'create';

            return;
        }

        $configInArray = preg_split('/\n/', $processConfigData);

        foreach ($configInArray as $configParam) {
            list($optionName, $optionValue) = explode('=', $configParam);

            $optionName = trim($optionName);
            $optionValue = trim($optionValue);

            if ($this->hasProperty($optionName)) {
                $this->$optionName = $optionValue;
            }
        }
    }

    /**
     * Delete specified process configurations.
     *
     * @param bool|false $backup
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function deleteProcess($backup = false)
    {
        return $this->_config->deleteGroup($backup);
    }

    /**
     * Save configurations of current instance of class to config file via
     * ConfigFileHandler object.
     *
     * @return bool|int
     */
    public function saveProcessConfig()
    {
        $configInArray = [];

        // Collect object properties to array
        foreach ($this->_allowedConfigOptions as $optionName) {
            $configInArray[] = $optionName . '=' . $this->$optionName;
        }

        $configString = implode("\n", $configInArray);

        // Save process config depends on state
        if ($this->_state == 'create') {
            return $this->_config->createConfig(
                $this->_programName, $configString
            );
        } else {
            return $this->_config->saveConfig($configString);
        }
    }

    /**
     * @param array $processData
     *
     * @return bool
     */
    public function createGroup(array $processData)
    {
        foreach ($processData as $optionName => $optionValue) {
            if ($this->hasProperty($optionName)) {
                $this->$optionName = $optionValue;
            }
        }

        return $this->saveProcessConfig() ? true : false;
    }

    /**
     * @return bool|mixed
     */
    public function addNewGroupProcess()
    {
        $this->setNumprocs($this->getNumprocs() + 1);

        return $this->saveProcessConfig() ? true : false;
    }

    /**
     * @return bool|mixed
     */
    public function deleteGroupProcess()
    {
        $currentProcessNumber = $this->getNumprocs();

        if ($currentProcessNumber == 1) {
            return false;
        }

        $this->setNumprocs($currentProcessNumber - 1);

        return $this->saveProcessConfig() ? true : false;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->_command = $command;
    }

    /**
     * @return string
     */
    public function getProcess_name()
    {
        return $this->_process_name;
    }

    /**
     * @param string $process_name
     */
    public function setProcess_name($process_name)
    {
        $this->_process_name = $process_name;
    }

    /**
     * @return int
     */
    public function getNumprocs()
    {
        return $this->_numprocs;
    }

    /**
     * @param int $numprocs
     */
    public function setNumprocs($numprocs)
    {
        $this->_numprocs = $numprocs;
    }

    /**
     * @return int
     */
    public function getNumprocs_start()
    {
        return $this->_numprocs_start;
    }

    /**
     * @param int $numprocs_start
     */
    public function setNumprocs_start($numprocs_start)
    {
        $this->_numprocs_start = $numprocs_start;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * @param $priority
     *
     * @throws ProcessConfigException
     */
    public function setPriority($priority)
    {
        if ((int)$priority > 999) {
            throw new ProcessConfigException('Invalid process priority param.');
        }

        $this->_priority = (int)$priority;
    }

    /**
     * @return boolean
     */
    public function getAutostart()
    {
        return $this->_autostart;
    }

    /**
     * @param boolean $autostart
     */
    public function setAutostart($autostart)
    {
        $this->_autostart = (int)$autostart;
    }

    /**
     * @return int
     */
    public function getStartretries()
    {
        return $this->_startretries;
    }

    /**
     * @param int $startretries
     */
    public function setStartretries($startretries)
    {
        $this->_startretries = $startretries;
    }

    /**
     * @return string
     */
    public function getAutorestart()
    {
        return $this->_autorestart;
    }

    /**
     * @param $autorestart
     *
     * @throws ProcessConfigException
     */
    public function setAutorestart($autorestart)
    {
        if (!in_array($autorestart, ['false', 'unexpected', 'true'])) {
            throw new ProcessConfigException(
                'Invalid process auto restart param.'
            );
        }

        $this->_autorestart = $autorestart;
    }

    /**
     * @return string
     */
    public function getExitcodes()
    {
        return $this->_exitcodes;
    }

    /**
     * @param string $exitcodes
     */
    public function setExitcodes($exitcodes)
    {
        $this->_exitcodes = $exitcodes;
    }

    /**
     * @return string
     */
    public function getStopsignal()
    {
        return $this->_stopsignal;
    }

    /**
     * @param string $stopsignal
     *
     * @throws ProcessConfigException
     */
    public function setStopsignal($stopsignal)
    {
        if (
            !in_array(
                $stopsignal,
                ['TERM', 'HUP', 'INT', 'QUIT', 'KILL', 'USR1', 'USR2']
            )
        ) {
            throw new ProcessConfigException('Invalid stop signal value.');
        }

        $this->_stopsignal = $stopsignal;
    }

    /**
     * @return int
     */
    public function getStopwaitsecs()
    {
        return $this->_stopwaitsecs;
    }

    /**
     * @param int $stopwaitsecs
     */
    public function setStopwaitsecs($stopwaitsecs)
    {
        $this->_stopwaitsecs = $stopwaitsecs;
    }

    /**
     * @return int
     */
    public function getStartsecs()
    {
        return $this->_startsecs;
    }

    /**
     * @param int $startsecs
     */
    public function setStartsecs($startsecs)
    {
        $this->_startsecs = $startsecs;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }
}