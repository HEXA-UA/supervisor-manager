<?php

namespace supervisormanager\components\supervisor\control;

use supervisormanager\components\supervisor\config\ProcessConfig;
use supervisormanager\components\supervisor\ConnectionInterface;
use supervisormanager\components\supervisor\exceptions\ProcessException;
use supervisormanager\components\supervisor\Supervisor;

/**
 * Class Process
 *
 * @package supervisormanager\components\supervisor\control
 */
class Process extends Supervisor
{
    /**
     * @var string
     */
    private $_processName;

    /**
     * Process constructor.
     *
     * @param string              $processName
     * @param ConnectionInterface $connection
     */
    public function __construct($processName, ConnectionInterface $connection)
    {
        $this->_processName = $processName;

        parent::__construct($connection);
    }

    /**
     * Stop single supervisor child process by passed passed name.
     *
     * @return mixed
     */
    public function stopProcess()
    {
        return $this->_connection->callMethod(
            'supervisor.stopProcess', [$this->_processName]
        );
    }

    /**
     * Start single supervisor child process by passed passed name.
     *
     * @return mixed
     */
    public function startProcess()
    {
        return $this->_connection->callMethod(
            'supervisor.startProcess', [$this->_processName]
        );
    }

    /**
     * Get full info of single supervisor child process by passed passed name.
     *
     * @return mixed
     */
    public function getProcessInfo()
    {
        return $this->_connection->callMethod(
            'supervisor.getProcessInfo', [$this->_processName]
        );
    }

    /**
     * Get standart log or errors output of supervisor child process.
     *
     * @param $outputType
     *
     * @return string
     * @throws ProcessException
     */
    public function getProcessOutput($outputType)
    {
        if (!in_array($outputType, ['stderr_logfile', 'stdout_logfile'])) {
            throw new ProcessException(
                'Specified incorrect type of process output.'
            );
        }

        return file_get_contents($this->getProcessInfo()[$outputType]);
    }

    /**
     * @param $processName
     * @codeCoverageIgnore
     *
     * @return int
     * @throws ProcessException
     */
    public static function getProcessPriority($processName)
    {
        return (new ProcessConfig($processName))->getPriority();
    }
}