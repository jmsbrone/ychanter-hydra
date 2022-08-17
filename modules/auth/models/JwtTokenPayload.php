<?php

namespace app\modules\auth\models;

use yii\base\Model;

/**
 * JWT token payload data.
 */
class JwtTokenPayload extends Model
{
    /** @var int User id */
    public int $user_id = 0;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer']
        ];
    }
}
