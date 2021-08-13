<?php

use supervisormanager\SupervisorAsset;
use yii\helpers\Json;
use yii\helpers\Url;

SupervisorAsset::register($this);

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Supervisor';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_modal', ['supervisorGroupForm' => $supervisorGroupForm]);
echo $this->render('create-group', ['supervisorGroupForm' => $supervisorGroupForm]);
$this->registerJs('var supervisorManager = ' . Json::encode([
    'urls' => [
        'supervisorControl' => Url::to(['/supervisor/default/supervisor-control']),
        'processControl' => Url::to(['/supervisor/default/process-control']),
        'groupControl' => Url::to(['/supervisor/default/group-control']),
        'processConfigControl' => Url::to(['/supervisor/default/process-config-control']),
        'countGroupProcesses' => Url::to(['/supervisor/default/count-group-processes']),
        'getProcessLog' => Url::to(['/supervisor/default/get-process-log']),
    ],
]) . ';', yii\web\View::POS_HEAD);
?>

<style>
    .container {
        width: 100%;
    }
</style>

<div class="supervisor-index">
    <?php \yii\widgets\Pjax::begin(['id' => 'supervisor', 'timeout' => 5000]); ?>
    <?php echo $this->render('_grid', ['dataProvider' => $dataProvider]);?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>