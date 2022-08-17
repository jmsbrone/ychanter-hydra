<?php

namespace app\modules\auth\repositories;

use app\infrastructure\prototypes\BaseRepository;
use app\modules\auth\domains\LoginPswEntity;
use app\modules\auth\spi\LoginPswEntityRepositorySPI;

/**
 * Repository for LoginPswEntity model that provides login+password authentication.
 */
class LoginPswEntityRepository extends BaseRepository implements LoginPswEntityRepositorySPI
{
    /**
     * @inheritDoc
     */
    public function tableName(): string
    {
        return '{{%login_psw_entity}}';
    }

    /**
     * @inheritDoc
     */
    protected function domainClass(): string
    {
        return LoginPswEntity::class;
    }
}
