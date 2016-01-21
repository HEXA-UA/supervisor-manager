<?php

namespace supervisormanager\components\filters;

use Yii;
use yii\base\ActionEvent;
use yii\base\ActionFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;

class AjaxAccess extends ActionFilter
{
    public $defaultUrl = ['site/index'];

    /**
     * Declares event handlers for the [[owner]]'s events.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    /**
     * @param ActionEvent $event
     * @return boolean
     * @throws MethodNotAllowedHttpException when the request method is not allowed.
     */
    public function beforeAction($event)
    {
        if (Yii::$app->request->isAjax) {
            return parent::beforeAction($event);
        } else {
            $this->denyAccess(Yii::$app->user);
        }
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param Yii\web\User $user the current user
     * @throws Yii\web\ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->getIsGuest()) {
            $user->loginRequired();
        } else {
            $this->ajaxOnly();
        }
    }

    public function ajaxOnly()
    {
        if ($this->defaultUrl !== null) {
            $defUrl = (array)$this->defaultUrl;
            if ($defUrl[0] !== Yii::$app->requestedRoute)
                return Yii::$app->getResponse()->redirect($this->defaultUrl);
        }
        throw new ForbiddenHttpException('Only ajax!');
    }
}