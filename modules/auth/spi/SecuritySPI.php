<?php

namespace app\modules\auth\spi;

/**
 * Port for security operations
 */
interface SecuritySPI
{
    /**
     * Validates password against given hash.
     *
     * @param string $password Raw password to check
     * @param string $hash Password hash to check against
     * @return bool True if password matches the hash, false otherwise
     */
    public function validatePassword(string $password, string $hash): bool;

    /**
     * Generates hash for given password.
     *
     * @param string $password Raw password
     * @return string Password hash
     */
    public function generatePasswordHash(string $password): string;
}
