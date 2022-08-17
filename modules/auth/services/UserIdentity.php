<?php

namespace app\modules\auth\services;

use app\core\PortManager;
use app\modules\auth\domains\User;
use app\modules\auth\spi\JwtSPI;
use app\modules\auth\spi\UserRepositorySPI;
use yii\web\IdentityInterface;

/**
 * Class for using with Yii authentication
 */
class UserIdentity implements IdentityInterface
{
    /** @var User|null Actual user model attached to current identity */
    public ?User $userModel = null;

    /**
     * @inheritDoc
     */
    public static function findIdentity($id)
    {
        $identity = null;
        $userRepository = PortManager::getPort(UserRepositorySPI::class);
        $user = $userRepository->findOne(['id' => $id]);
        if ($user !== null) {
            $identity = new static();
            $identity->userModel = $user;
        }

        return $identity;
    }

    /**
     * @inheritDoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $jwtService = PortManager::getPort(JwtSPI::class);
        $payload = $jwtService->decode($token);

        return static::findIdentity($payload->user_id);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->userModel?->id;
    }

    /**
     * @inheritDoc
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }
}
