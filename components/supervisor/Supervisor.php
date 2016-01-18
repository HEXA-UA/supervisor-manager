<?php

namespace frontend\modules\supervisor\components\supervisor;

use yii\base\Component;

/**
 * Class Supervisor
 *
 * @package frontend\modules\supervisor\components\supervisor
 */
class Supervisor extends Component
{
    const EVENT_CONFIG_CHANGED = 'configChangedEvent';

    /**
     * @var ConnectionInterface
     */
    public $_connection;

    /**
     * Supervisor constructor.
     *
     * @param ConnectionInterface $connection
     * @param array               $config
     */
    public function __construct(
        ConnectionInterface $connection, array $config = []
    ) {
        $this->_connection = $connection;

        parent::__construct($config);
    }

    /**
     * Update supervisor config file.
     *
     * @return int
     */
    public function configChangedEvent()
    {
        exec('supervisorctl update', $output, $status);

        return $status;
    }
}