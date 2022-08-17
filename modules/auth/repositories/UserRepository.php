<?php

namespace app\modules\auth\repositories;

use app\infrastructure\prototypes\BaseRepository;
use app\modules\auth\domains\User;
use app\modules\auth\spi\UserRepositorySPI;

/**
 * Repository for User model
 */
class UserRepository extends BaseRepository implements UserRepositorySPI
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * @inheritDoc
     */
    protected function domainClass(): string
    {
        return User::class;
    }
}
