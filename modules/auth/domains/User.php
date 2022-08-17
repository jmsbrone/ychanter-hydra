<?php

namespace app\modules\auth\domains;

use yii\base\Model;

/**
 * Model for user domain
 */
class User extends Model
{
    public ?int $id = null;
    public string $name = '';
    public string $created_at = '';
    public string $updated_at = '';
}
