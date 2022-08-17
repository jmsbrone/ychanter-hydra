<?php

namespace app\modules\auth\services;

use app\modules\auth\models\JwtTokenPayload;
use app\modules\auth\spi\JwtSPI;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;
use yii\caching\CacheInterface;

/**
 * Adapter for JWT port
 */
class JwtService implements JwtSPI
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function sign(JwtTokenPayload $payload): string
    {
        if (!$payload->validate()) {
            Yii::debug('Invalid JWT payload received: ' . serialize($payload));
            throw new Exception();
        }

        $key = Yii::$app->params['jwt']['secret'];
        $algorithm = Yii::$app->params['jwt']['algorithm'];

        $token = JWT::encode($payload->getAttributes(), $key, $algorithm);
        $tokenCache = $this->getTokenCache();
        $tokenCache->set($token, $payload->getAttributes());

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function decode(string $token): JwtTokenPayload
    {
        $tokenCache = $this->getTokenCache();
        $decodedPayloadData = $tokenCache->get($token);
        if (empty($decodedPayloadData)) {
            $key = Yii::$app->params['jwt']['secret'];
            $algorithm = Yii::$app->params['jwt']['algorithm'];
            $decodedPayloadData = (array)JWT::decode($token, new Key($key, $algorithm));

            $tokenCache->set($token, $decodedPayloadData);
        }

        return new JwtTokenPayload($decodedPayloadData);
    }

    /**
     * @return CacheInterface
     */
    protected function getTokenCache(): CacheInterface
    {
        return Yii::$app->cache;
    }
}
