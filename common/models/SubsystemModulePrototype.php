<?php

namespace app\common\models;

use app\common\traits\GenericModuleInfo;

/**
 * Prototype for module information.
 */
abstract class SubsystemModulePrototype
{
    use GenericModuleInfo;

    /** @var bool Whether the module is installed in the subsystem */
    public bool $installed = false;
    /** @var bool Whether the module is active in the subsystem */
    public bool $active = false;
    /** @var string|null If module is installed must contain the installed version */
    public ?string $installedVersion = null;

    /**
     * Returns ID of the subsystem this module is meant for.
     *
     * @return string
     */
    abstract public function getSubsystemID(): string;
}
