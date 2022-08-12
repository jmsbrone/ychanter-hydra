<?php

namespace app\core\exceptions;

use Exception;
use Throwable;

/**
 * Exception to specify that specified module is not installed and thus cannot be used
 * in requested operation.
 */
class ModuleNotInstalledException extends Exception
{
    public function __construct(string $moduleId, ?Throwable $previous = null)
    {
        $errorMessage = "Module $moduleId is not installed";

        parent::__construct($errorMessage, 0x0102, $previous);
    }
}
