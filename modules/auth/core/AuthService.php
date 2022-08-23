<?php

namespace app\modules\auth\core;

use app\modules\auth\api\AuthServiceAPI;
use app\modules\auth\domains\LoginPswEntity;
use app\modules\auth\domains\User;
use app\modules\auth\models\JwtTokenPayload;
use app\modules\auth\spi\JwtSPI;
use app\modules\auth\spi\LoginPswEntityRepositorySPI;
use app\modules\auth\spi\SecuritySPI;
use app\modules\auth\spi\UserRepositorySPI;
use Exception;
use yii\base\UserException;

/**
 * Service for working with authentication and authorization of currently logged-in user.
 */
class AuthService implements AuthServiceAPI
{
    /** @var string Login for root user account */
    public const ROOT_USER_LOGIN = 'root';

    /**
     * @param UserRepositorySPI $userRepository Repository for working with users
     * @param LoginPswEntityRepositorySPI $loginPswEntityRepository Repository for login+password entities
     * @param SecuritySPI $security Security service
     * @param JwtSPI $jwtService Service for working with JWT tokens
     */
    public function __construct(
        protected readonly UserRepositorySPI $userRepository,
        protected readonly LoginPswEntityRepositorySPI $loginPswEntityRepository,
        protected readonly SecuritySPI $security,
        protected readonly JwtSPI $jwtService,
    ) {

    }

    /**
     * @inheritDoc
     */
    public function resetRootPassword(string $password): void
    {
        $loginPswEntity = $this->getRootLoginEntity();
        if ($loginPswEntity === null) {
            $user = new User();
            $user->name = self::ROOT_USER_LOGIN;
            $this->userRepository->save($user);

            $loginPswEntity = new LoginPswEntity([
                'id' => $user->id,
                'login' => self::ROOT_USER_LOGIN,
            ]);
        }

        $loginPswEntity->passwordHash = $this->security->generatePasswordHash($password);
        $this->loginPswEntityRepository->save($loginPswEntity);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getAccessTokenByLoginPsw(string $login, string $password): string
    {
        $loginPswEntity = $this->getLoginEntity($login);
        if ($loginPswEntity === null
            || !$this->security->validatePassword($password, $loginPswEntity->passwordHash)) {
            throw new UserException('Not authorized');
        }

        $payload = new JwtTokenPayload([
            'user_id' => $loginPswEntity->id,
        ]);

        return $this->jwtService->sign($payload);
    }

    /**
     * Retrieves entity for authenticating via login+password.
     *
     * @param string $login Login for lookup
     * @return LoginPswEntity|null
     */
    public function getLoginEntity(string $login): ?LoginPswEntity
    {
        return $this->loginPswEntityRepository->findOne(['login' => $login]);
    }

    /**
     * Returns root user login entity.
     *
     * @return LoginPswEntity|null
     */
    protected function getRootLoginEntity(): ?LoginPswEntity
    {
        return $this->getLoginEntity(self::ROOT_USER_LOGIN);
    }
}
