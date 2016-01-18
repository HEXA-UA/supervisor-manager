<?php

namespace frontend\modules\supervisor\controllers;

use common\components\filters\AjaxAccess;
use frontend\modules\supervisor\components\supervisor\config\ConfigFileHandler;
use frontend\modules\supervisor\components\supervisor\config\ProcessConfig;
use frontend\modules\supervisor\components\supervisor\control\Group;
use frontend\modules\supervisor\components\supervisor\control\MainProcess;
use frontend\modules\supervisor\components\supervisor\control\Process;
use frontend\modules\supervisor\components\supervisor\exceptions\ConnectionException;
use frontend\modules\supervisor\components\supervisor\exceptions\SupervisorException;
use frontend\modules\supervisor\components\supervisor\Supervisor;
use frontend\modules\supervisor\models\SupervisorGroupForm;
use yii\base\Event;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            [
                'class' => ContentNegotiator::className(),
                'except' => [
                    'index',
                    'restore-from-backup',
                    'create-group',
                    'start-supervisor'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => AjaxAccess::className(),
                'except' => [
                    'index',
                    'restore-from-backup',
                    'create-group',
                    'start-supervisor'
                ],
            ]
        ];
    }

    /**
     * Lists all Domain models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        try {
            $supervisor = $this->_supervisorMainProcess();
        } catch (ConnectionException $error) {
            return $this->_errorHandle($error);
        }

        $groups = $supervisor->getAllProcessesByGroup();

        foreach ($groups as $groupName => &$group) {

            $group['group'] = $groupName;

            $group['processList'] = new ArrayDataProvider(
                ['allModels' => $group['processList'],
                 'pagination' => ['pageSize' => 5, 'pageParam' => $groupName]
                ]
            );
        }

        $supervisorGroupForm = new SupervisorGroupForm();

        $dataProvider = new ArrayDataProvider(['models' => $groups]);

        return $this->_renderProcess(
            'index',
            [
                'supervisorGroupForm' => $supervisorGroupForm,
                'dataProvider' => $dataProvider
            ]
        );
    }

    public function actionStartSupervisor()
    {
        MainProcess::forceStart(1500);

        $this->redirect(Url::to('/supervisor/default/index'));
    }

    public function actionCreateGroup()
    {
        $model = new SupervisorGroupForm;

        $request = \Yii::$app->request;

        if ($model->load($request->post())) {
            $model->saveGroup();
        }

        Event::trigger(
            Supervisor::className(), Supervisor::EVENT_CONFIG_CHANGED
        );

        $this->redirect(Url::to('/supervisor/default/index'));
    }

    public function actionRestoreFromBackup()
    {
        (new ConfigFileHandler())->restoreFromBackup();

        Event::trigger(
            Supervisor::className(), Supervisor::EVENT_CONFIG_CHANGED
        );

        $this->redirect(Url::to('/supervisor/default/index'));
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionProcessControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $process = $this->_supervisorProcess($request->post('processName'));

            if ($process->hasMethod($actionType)) {
                $process->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionSupervisorControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $supervisor = $this->_supervisorMainProcess();

            if ($supervisor->hasMethod($actionType)) {
                $supervisor->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * Responsible for the process control of the entire group.
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGroupControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $group = $this->_supervisorGroup(
                $request->post('groupName')
            );

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    public function actionProcessConfigControl()
    {
        $request = \Yii::$app->request;

        $actionType = $request->post('actionType');

        $response = ['isSuccessful' => true];

        try {
            $group = new ProcessConfig(
                $request->post('groupName')
            );

            if ($group->hasMethod($actionType)) {
                $group->$actionType();
            }

            Event::trigger(
                Supervisor::className(), Supervisor::EVENT_CONFIG_CHANGED
            );
        } catch (SupervisorException $error) {
            $response = [
                'isSuccessful' => false, 'error' => $error->getMessage()
            ];
        }

        return $response;
    }

    /**
     * Get log or errors output of single supervisor process.
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGetProcessLog()
    {
        $request = \Yii::$app->request;

        $response = ['isSuccessful' => false];

        try {
            $processLog = $this->_supervisorProcess(
                $request->post('processName')
            )->getProcessOutput($request->post('logType'));

            $response = [
                'isSuccessful' => true,
                'processLog' => $processLog ?: 'No logs'
            ];
        } catch (SupervisorException $error) {
            $response = ['error' => $error->getMessage()];
        }

        return $response;
    }

    /**
     * @param $view
     * @param $data
     *
     * @return string
     */
    private function _renderProcess($view, $data)
    {
        if(\Yii::$app->request->getHeaders()->has('X-PJAX')) {
            return $this->renderAjax($view, $data);
        } else {
            return $this->render($view, $data);
        }
    }

    /**
     * @param \Exception $error
     *
     * @return string
     */
    private function _errorHandle(\Exception $error)
    {
        return $this->render('error', ['message' => $error->getMessage()]);
    }

    /**
     * @return MainProcess
     * @throws \yii\base\InvalidConfigException
     */
    private function _supervisorMainProcess()
    {
        return \Yii::$container->get(MainProcess::className());
    }

    /**
     * @param $processName
     *
     * @return Process
     * @throws \yii\base\InvalidConfigException
     */
    private function _supervisorProcess($processName)
    {
        return \Yii::$container->get(Process::className(), [$processName]);
    }

    /**
     * @param $groupName
     *
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    private function _supervisorGroup($groupName)
    {
        return \Yii::$container->get(Group::className(), [$groupName]);
    }
}
