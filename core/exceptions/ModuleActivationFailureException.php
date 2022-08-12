<?php

namespace app\core\exceptions;

use Exception;
use Throwable;

/**
 * Exception is thrown when module activation in a subsystem failed.
 */
class ModuleActivationFailureException extends Exception
{
    public function __construct(string $moduleId, string $subsystem, string $installationError, ?Throwable $previous = null)
    {
        $errorMessage = "Failed to activate module `$moduleId` for service `$subsystem`: $installationError";

        parent::__construct($errorMessage, 0x0103, $previous);
    }
}
