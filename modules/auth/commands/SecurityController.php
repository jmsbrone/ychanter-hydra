<?php

namespace app\modules\auth\commands;

use app\modules\auth\api\AuthServiceAPI;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Security actions CLI.
 *
 * Controller contains actions that can affect security of the application and
 * must be used with caution.
 *
 * @uses \app\modules\auth\commands\SecurityController::actionSetRootPsw()
 * @uses \app\modules\auth\commands\SecurityController::actionDropRbacCache()
 * @uses \app\modules\auth\commands\SecurityController::actionInitRbac()
 * @uses \app\modules\auth\commands\SecurityController::actionDropRbac()
 * @uses \app\modules\auth\commands\SecurityController::actionDropCache()
 */
class SecurityController extends Controller
{
    /**
     * @param $id
     * @param $module
     * @param AuthServiceAPI $authService
     * @param array $config
     */
    public function __construct($id, $module, protected readonly AuthServiceAPI $authService, array $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * Sets new password for root user.
     *
     * @param string $password New password
     *
     * @return int Exit code
     */
    public function actionSetRootPsw(string $password): int
    {
        if (strlen($password) < 4) {
            echo 'Password must be at least 4 characters long!' . PHP_EOL;
            return ExitCode::DATAERR;
        }

        $this->authService->resetRootPassword($password);

        echo 'Root password has been reset.' . PHP_EOL;

        return ExitCode::OK;
    }
}
