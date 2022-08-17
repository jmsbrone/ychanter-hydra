<?php

namespace app\modules\auth\spi;

use app\modules\auth\models\JwtTokenPayload;

/**
 * Port for working with JWT token based authentication.
 */
interface JwtSPI
{
    /**
     * Returns JWT token with given payload.
     *
     * @param JwtTokenPayload $payload Data to sign
     * @return string
     */
    public function sign(JwtTokenPayload $payload): string;

    /**
     * Decodes JWT token and returns its payload.
     *
     * @param string $token JWT token
     * @return JwtTokenPayload
     */
    public function decode(string $token): JwtTokenPayload;
}
