<?php

namespace app\modules\auth\domains;

use yii\base\Model;

/**
 * Model for login+password authentication.
 */
class LoginPswEntity extends Model
{
    public ?int $id = null;
    public string $login = '';
    public string $passwordHash = '';
    public string $created_at = '';
    public string $updated_at = '';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id'], 'int'],
            [['login', 'passwordHash'], 'string', 'max' => 255],
            [['login', 'passwordHash'], 'required'],
        ];
    }
}
