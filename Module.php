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
    public $controllerNamespace = 'frontend\modules\supervisor\controllers';

    public function init()
    {
        parent::init();

        Event::on(Supervisor::className(), Supervisor::EVENT_CONFIG_CHANGED,
            function () {
                exec('supervisorctl update', $output, $status);
            }
        );

        \Yii::configure($this, require(__DIR__ . '/config.php'));

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
