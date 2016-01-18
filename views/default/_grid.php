<?php


use frontend\modules\supervisor\components\gridView\GridView;
use frontend\modules\supervisor\components\widgets\ProcessPriorityWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

echo GridView::widget(
    [
        'dataProvider' => $dataProvider,
        'tableTitle' => 'Process status',
        'beforeItems' => $this->render('_supervisorControl'),
        'layout' => '{items}{pager}',
        'columns'      => [
            [
                'label' => 'Group Name',
                'value' => 'group',
            ],
            [
                'label' => 'Group Control',
                'format' => 'raw',
                'contentOptions' => ['class' => 'groupOptions'],
                'value' => function($model) {
                    return $this->render('_groupControl', ['groupName' => $model['group']]);
                }
            ],
            [
                'label' => 'Group Process List',
                'format' => 'raw',
                'contentOptions' => ['class' => 'processList'],
                'value' => function($model) {
                    return \yii\grid\GridView::widget(
                        [
                            'dataProvider' => $model['processList'],
                            'layout' => '{items}{pager}',
                            'columns'      => [
                                [
                                    'label' => 'Process Name',
                                    'value' => 'name',
                                    'options' => ['width' => '13%'],
                                ],
                                [
                                    'label' => 'Up Time',
                                    'format' => 'raw',
                                    'options' => ['width' => '7%'],
                                    'value' => function($model) {
                                        preg_match(
                                            '/\d{1,2}(?:\:\d{1,2}){2}/',
                                            $model['description'],
                                            $upTime
                                        );

                                        $classes = [
                                            'RUNNING' => 'success',
                                            'STOPPED' => 'warning',
                                            'SHUTDOWN' => 'primary',
                                            'FATAL' => 'danger'
                                        ];

                                        $class = 'label label-' . ArrayHelper::getValue(
                                                $classes, $model['statename'], 'warning'
                                            );

                                        return isset($upTime[0]) ?$upTime[0] : Html::tag(
                                            'span', $model['statename'], ['class' => $class]
                                        );
                                    }
                                ],
                                [
                                    'label' => 'Last stop',
                                    'options' => ['width' => '11%'],
                                    'value' => function($model) {
                                        return $model['stop'] ? date('Y-m-d H:i:s', $model['stop']) : "Wasn't stopped";
                                    }
                                ],
                                [
                                    'label' => 'Status',
                                    'format' => 'html',
                                    'contentOptions' => ['align' => 'center'],
                                    'options' => ['width' => '7%'],
                                    'value' => function ($model) {
                                        $classes = [
                                            'RUNNING' => 'success',
                                            'STOPPED' => 'warning',
                                            'SHUTDOWN' => 'primary',
                                            'FATAL' => 'danger'
                                        ];

                                        $class = 'label label-' . ArrayHelper::getValue(
                                                $classes, $model['statename'], 'warning'
                                            );

                                        return Html::tag(
                                            'span', $model['statename'], ['class' => $class]
                                        );
                                    }
                                ],
                                [
                                    'label' => 'Process ID',
                                    'contentOptions' => ['align' => 'center'],
                                    'options' => ['width' => '7%'],
                                    'value' => 'pid'
                                ],
                                [
                                    'label' => 'Started',
                                    'options' => ['width' => '13%'],
                                    'value' => function($model) {
                                        return $model['start'] ? date('Y-m-d H:i:s', $model['start']) : "Wasn't started";
                                    }
                                ],
                                [
                                    'label' => 'Priority',
                                    'format' => 'raw',
//                                    'options' => ['width' => '10%'],
                                    'value' => function($model) {
                                        return ProcessPriorityWidget::widget(['priority' => $model['priority']]);
                                    }
                                ],
                                [
                                    'label' => 'Output',
                                    'format' => 'raw',
                                    'contentOptions' => ['align' => 'center'],
                                    'value' => function($model) {
                                        return Html::button(
                                            'Show output',
                                            [
                                                'class' => 'btn btn-default showLog',
                                                'data-process-name' => $model['group'] . ':' . $model['name'],
                                                'data-log-type' => 'stdout_logfile'
                                            ]
                                        );
                                    }
                                ],
                                [
                                    'label' => 'Error Log',
                                    'format' => 'raw',
                                    'contentOptions' => ['align' => 'center'],
                                    'value' => function($model) {
                                        return Html::button(
                                            'Show errors',
                                            [
                                                'class' => 'btn btn-default showLog',
                                                'data-process-name' => $model['group'] . ':' . $model['name'],
                                                'data-log-type' => 'stderr_logfile'
                                            ]
                                        );
                                    }
                                ],
                                [
                                    'label' => 'Process Control',
                                    'contentOptions' => ['align' => 'center'],
                                    'format' => 'raw',
//                                    'options' => ['width' => '5%'],
                                    'value' => function($model) {
                                        $iconClass = 'stop';

                                        $dataAction = 'stopProcess';

                                        if ($model['state'] == 0) {
                                            $iconClass = 'play';

                                            $dataAction = 'startProcess';
                                        }

                                        $content = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconClass"]);

                                        return Html::tag(
                                            'a',
                                            $content,
                                            [
                                                'class' => 'btn btn-default processControl',
                                                'data-action-type' => $dataAction,
                                                'data-process-name' => $model['group'] . ':' . $model['name']
                                            ]
                                        );
                                    }
                                ]
                            ]
                        ]);
                }
            ],
        ],
    ]
); ?>