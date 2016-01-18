<?php

namespace frontend\modules\supervisor;

use yii\base\Event;
use Zend\XmlRpc\Client;
use frontend\modules\supervisor\components\supervisor\Supervisor;
use frontend\modules\supervisor\components\supervisor\ConnectionInterface;

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
