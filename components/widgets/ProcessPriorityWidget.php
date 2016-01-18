<?php

namespace supervisormanager\components\widgets;

use yii\base\Widget;

/**
 * Class ProcessPriorityWidget
 *
 * @package supervisormanager\components\widgets
 */
class ProcessPriorityWidget extends Widget
{
    /**
     * @var int
     */
    public $priority;

    /**
     * @var int
     */
    public $maxPriority = 999;

    /**
     * @var int
     */
    public $minPriority = 0;

    /**
     * @var array
     */
    public $classesRange = [
        'danger' => 33,
        'warning' => 66,
        'success' => 100,
    ];

    public function run()
    {
        parent::run();

        return $this->render(
            'priority',
            [
                'priority' => $this->priority,
                'progressBarWidth' => $this->_getPriorityInPercent(),
                'progressBarClass' => $this->_getProgressBarClass()
            ]
        );
    }

    /**
     * @return int
     */
    private function _getPriorityInPercent()
    {
        return $this->priority * (100 / $this->maxPriority);
    }

    /**
     * @return string
     */
    private function _getProgressBarClass()
    {
        $progressBarWidth = $this->_getPriorityInPercent();

        $resultClass = '';

        foreach ($this->classesRange as $class => $range) {
            if ($progressBarWidth <= $range) {
                $resultClass = $class; break;
            }
        }

        $resultClass = $resultClass ?: array_pop($this->classesRange);

        return $resultClass;
    }
}