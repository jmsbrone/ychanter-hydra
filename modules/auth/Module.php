<?php

namespace app\modules\auth;

use app\modules\auth\api\AuthServiceAPI;
use app\modules\auth\core\AuthService;
use app\modules\auth\repositories\LoginPswEntityRepository;
use app\modules\auth\repositories\UserRepository;
use app\modules\auth\services\JwtService;
use app\modules\auth\services\SecurityService;
use app\modules\auth\spi\JwtSPI;
use app\modules\auth\spi\LoginPswEntityRepositorySPI;
use app\modules\auth\spi\SecuritySPI;
use app\modules\auth\spi\UserRepositorySPI;
use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application;

/**
 * Module for authorization/authentication system.
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var Module|null Module instance reference
     * There can only be one module in any system.
     * Module instance is stored in order to access module components
     * from within the module core without passing down the instance.
     */
    protected static ?Module $instance = null;

    /**
     * Returns singleton instance of the module.
     *
     * @return static
     */
    public static function getModuleInstance(): static
    {
        return static::$instance;
    }

    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = 'app\modules\auth\commands';
        }

        static::$instance = $this;

        Yii::$container->setSingletons([
            AuthServiceAPI::class => AuthService::class,
            JwtSPI::class => JwtService::class,
            LoginPswEntityRepositorySPI::class => LoginPswEntityRepository::class,
            SecuritySPI::class => SecurityService::class,
            UserRepositorySPI::class => UserRepository::class,
        ]);
    }
}
