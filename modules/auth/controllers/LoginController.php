<?php

namespace app\modules\auth\controllers;

use app\modules\auth\api\AuthServiceAPI;
use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\base\UserException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * @uses \app\modules\auth\controllers\LoginController::actionIndex()
 */
class LoginController extends Controller
{
    /**
     * @inheritDoc
     * @param AuthServiceAPI $service
     */
    public function __construct($id, $module, protected AuthServiceAPI $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'verbs' => ['POST'],
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     * @throws UserException
     */
    #[ArrayShape(['access_token' => 'string'])]
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $login = Yii::$app->request->post('login');
        $password = Yii::$app->request->post('password');

        if (empty($login)) {
            throw new UserException('Missing login');
        }

        if (empty($password)) {
            throw new UserException('Missing password');
        }

        return [
            'access_token' => $this->service->getAccessTokenByLoginPsw($login, $password),
        ];
    }
}
