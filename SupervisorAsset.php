<?php

namespace frontend\modules\supervisor;

use yii\web\AssetBundle;

class SupervisorAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@frontend/modules/supervisor/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/main.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}