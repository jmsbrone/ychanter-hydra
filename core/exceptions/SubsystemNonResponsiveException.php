<?php

namespace app\core\exceptions;

use Exception;
use Throwable;

/**
 * Exception for specifying that some subsystem does not respond to requests.
 */
class SubsystemNonResponsiveException extends Exception
{
    public function __construct(string $subsystemID, ?Throwable $previous = null)
    {
        parent::__construct("Subsystem $subsystemID does not respond!", 0x0100, $previous);
    }
}
