<?php

namespace app\controllers;

use app\core\api\HubAPI;
use app\core\exceptions\ModuleActivationFailureException;
use app\core\exceptions\ModuleNotInstalledException;
use app\core\exceptions\SubsystemModuleVersionNotFoundException;
use app\core\exceptions\SubsystemNonResponsiveException;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Controller;
use yii\web\Response;

/**
 * Main controller for Hydra.
 */
class HubController extends Controller
{
    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
            ],
        ];
    }

    /**
     * Retrieving list of all modules.
     *
     * @return array
     * @throws InvalidConfigException
     * @throws SubsystemNonResponsiveException
     */
    public function actionListModules(): array
    {
        return $this->getService()->getModules();
    }

    /**
     * Returns available versions for a module.
     *
     * @param string $moduleId
     * @return array
     * @throws InvalidConfigException
     * @throws SubsystemNonResponsiveException
     */
    public function actionModuleVersions(string $moduleId): array
    {
        return $this->getService()->getModuleVersions($moduleId);
    }

    /**
     * Retrieve only installed modules.
     *
     * @return array
     * @throws InvalidConfigException
     * @throws SubsystemNonResponsiveException
     */
    public function actionListInstalledModules(): array
    {
        return $this->getService()->getInstalledModules();
    }

    /**
     * Installs a module with specific versions for subsystems.
     *
     * @return void
     * @throws InvalidConfigException
     * @throws SubsystemNonResponsiveException
     * @throws UserException
     * @throws SubsystemModuleVersionNotFoundException
     */
    public function actionInstallModule(): void
    {
        $data = (new DynamicModel(['moduleId', 'versions']))
            ->addRule('moduleId', 'string')
            ->addRule(['moduleId', 'versions'], 'required');

        $data->load(Yii::$app->request->post(), '');

        if (!$data->validate()) {
            throw new UserException(join('; ', $data->firstErrors));
        }

        $this->getService()->installModule($data->moduleId, $data->versions);
    }

    /**
     * Uninstalls a module completely.
     *
     * @return void
     * @throws InvalidConfigException
     * @throws SubsystemNonResponsiveException
     * @throws UserException
     * @throws ModuleNotInstalledException
     */
    public function actionUninstallModule(): void
    {
        $data = (new DynamicModel(['moduleId']))
            ->addRule('moduleId', 'string')
            ->addRule('moduleId', 'required');

        $data->load(Yii::$app->request->post(), '');

        if (!$data->validate()) {
            throw new UserException(join('; ', $data->firstErrors));
        }

        $this->getService()->uninstallModule($data->moduleId);
    }

    /**
     * Activates an installed module.
     *
     * @param string $moduleId
     * @return void
     * @throws InvalidConfigException
     * @throws ModuleNotInstalledException
     * @throws SubsystemNonResponsiveException
     * @throws ModuleActivationFailureException
     */
    public function actionActivateModule(string $moduleId): void
    {
        $this->getService()->activateModule($moduleId);
    }

    /**
     * Deactivates an active module.
     *
     * @param string $moduleId
     * @return void
     * @throws InvalidConfigException
     * @throws ModuleNotInstalledException
     * @throws SubsystemNonResponsiveException
     */
    public function actionDeactivateModule(string $moduleId): void
    {
        $this->getService()->deactivateModule($moduleId);
    }

    /**
     * Returns core API service instance.
     *
     * @return HubAPI
     * @throws InvalidConfigException
     */
    protected function getService(): HubAPI
    {
        return Yii::createObject(HubAPI::class);
    }
}
