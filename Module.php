<?php

namespace supervisormanager;

use supervisormanager\components\supervisor\ConnectionInterface;
use supervisormanager\components\supervisor\Supervisor;
use yii\base\Event;
use Zend\XmlRpc\Client;

/**
 * @property array supervisorConnection
 */
class Module extends \yii\base\Module
{
    /**
     * @var array Supervisor client authenticate data.
     */
    public $authData = [];

    /**
     * @var string
     */
    public $controllerNamespace = 'supervisormanager\controllers';

    public function init()
    {
        parent::init();

        Event::on(Supervisor::className(), Supervisor::EVENT_CONFIG_CHANGED,
            function () {
                exec('supervisorctl update', $output, $status);
            }
        );

        \Yii::configure($this, require(__DIR__ . '/config.php'));

        $this->params['supervisorConnection'] = array_merge(
            $this->params['supervisorConnection'], $this->authData
        );

        $this->registerIoC();
    }

    protected function registerIoC()
    {
        \Yii::$container->set(
            Client::class,
            function () {
                return new Client(
                    $this->params['supervisorConnection']['url']
                );
            }
        );

        \Yii::$container->set(
            ConnectionInterface::class,
            $this->params['supervisorConnection']
        );
    }
}
