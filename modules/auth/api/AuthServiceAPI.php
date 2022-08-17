<?php

namespace app\modules\auth\api;

/**
 * Service for working with authentication and authorization.
 */
interface AuthServiceAPI
{
    /**
     * Returns access token for login+psw authentication.
     *
     * @param string $login Login
     * @param string $password Password
     * @return string
     */
    public function getAccessTokenByLoginPsw(string $login, string $password): string;

    /**
     * Sets new root user password.
     *
     * @param string $password New password
     * @return void
     */
    public function resetRootPassword(string $password): void;
}
