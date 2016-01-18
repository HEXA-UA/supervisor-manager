<?php

use supervisormanager\SupervisorAsset;

SupervisorAsset::register($this);

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Supervisor';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_modal', ['supervisorGroupForm' => $supervisorGroupForm]);
echo $this->render('create-group', ['supervisorGroupForm' => $supervisorGroupForm]);
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