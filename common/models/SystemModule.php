<?php

namespace app\common\models;

use app\common\traits\GenericModuleInfo;

/**
 * Class represents a module for the overall system.
 */
class SystemModule
{
    use GenericModuleInfo;

    /** @var SubsystemModule[] Included modules for the subsystems */
    public array $subsystemModules = [];

    /**
     * Checks whether system module is installed.
     * System module is considered installed when all modules in subsystems are installed.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $active = true;
        foreach ($this->subsystemModules as $subsystemModule) {
            if (!$subsystemModule->active) {
                $active = false;
                break;
            }
        }

        return $active;
    }
}
