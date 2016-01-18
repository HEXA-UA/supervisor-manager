<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<div class="modal fade" id="createGroup" tabindex="-1" role="dialog" aria-labelledby="createGroup">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Create new processes group</h4>
            </div>
            <div class="modal-body">

                <div class="callout callout-warning">
                    <h4>Warning</h4>

                    <p>This action will restart all existing processes.</p>
                </div>

                <?php
                $form = ActiveForm::begin([
                    'id' => 'createGroupForm',
                    'action' => Url::to('/supervisor/default/create-group')
                ]); ?>

                <?= $form->field($supervisorGroupForm, 'groupName') ?>
                <?= $form->field($supervisorGroupForm, 'command') ?>
                <div class="row">
                    <div class="col-xs-4">
                        <?= $form->field($supervisorGroupForm, 'startretries') ?>
                    </div>
                    <div class="col-xs-4">
                        <?= $form->field($supervisorGroupForm, 'numprocs') ?>
                    </div>
                    <div class="col-xs-4">
                        <?= $form->field($supervisorGroupForm, 'priority')->textInput(['value' => 999]) ?>
                    </div>
                </div>
                <?= $form->field($supervisorGroupForm, 'autostart')->checkbox() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>