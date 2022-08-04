<?php

namespace app\core\exceptions;

/**
 * Exception to specify that available version of a subsystem module was not found.
 */
class SubsystemModuleVersionNotFoundException extends \Exception
{
    public function __construct(string $moduleId, string $subsystemID, ?\Throwable $previous = null)
    {
        $errorMessage = "Suitable module version of $moduleId is not found for service '$subsystemID'";

        parent::__construct($errorMessage, 0x0101, $previous);
    }
}
