<?php

namespace app\modules\auth\services;

use app\modules\auth\spi\SecuritySPI;
use Yii;
use yii\base\Exception;

/**
 * Security adapter based on Yii security component.
 */
class SecurityService implements SecuritySPI
{
    /**
     * @inheritDoc
     */
    public function validatePassword(string $password, string $hash): bool
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generatePasswordHash(string $password): string
    {
        return Yii::$app->security->generatePasswordHash($password);
    }
}
