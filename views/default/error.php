<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Supervisor';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-default">
    <div class="box-header with-border">
        <i class="fa fa-bullhorn"></i>

        <h3 class="box-title">Supervisor Error</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="callout callout-warning">
            <h4>Supervisor is not running!</h4>

            <p><?php echo isset($message) ? $message : 'Unknown error'?></p>

            <p>If this message was caused after configuration changes, you can <?php echo Html::a('restore previous configuration', Url::to('/supervisor/default/restore-from-backup'))?></p>
            <p>
                Otherwise you can <?php echo Html::a('start supervisor', Url::to('/supervisor/default/start-supervisor'))?>.
            </p>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->