<?php

namespace app\core\spi;

/**
 * Logger service.
 */
interface LoggerSPI
{
    /**
     * Adds information message to the log.
     *
     * @param string $message
     * @return void
     */
    public function info(string $message): void;
}
