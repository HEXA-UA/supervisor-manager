<?php

namespace supervisormanager\models;

use supervisormanager\components\supervisor\config\ProcessConfig;
use yii\base\Model;

class SupervisorGroupForm extends Model
{
    /**
     * @var string
     */
    public $groupName;

    /**
     * @var string
     */
    public $command;

    /**\
     * @var bool
     */
    public $autostart;

    /**
     * @var int
     */
    public $startretries;

    /**
     * @var int
     */
    public $numprocs;

    /**
     * @var
     */
    public $priority;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['groupName', 'command', 'startretries', 'numprocs'],
                'filter',
                'filter' => 'trim'
            ],
            [
                ['groupName', 'command', 'startretries', 'numprocs', 'priority'],
                'required'
            ],
            [['groupName', 'command'], 'string'],
            [['startretries', 'numprocs', 'priority'], 'integer'],
            [['numprocs'], 'integer', 'max' => 20],
            [['priority'], 'integer', 'min' => 1, 'max' => 999],
            [['autostart'], 'boolean'],
            [['autostart'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'groupName' => 'Group name',
            'command' => 'Process command',
            'startretries' => 'Number of start retry',
            'numprocs' => 'Number of processes',
            'autostart' => 'Auto start',
        ];
    }

    public function saveGroup()
    {
        $processConfig = new ProcessConfig($this->groupName);

        return $processConfig->createGroup($this->attributes) ? true : false;
    }
}