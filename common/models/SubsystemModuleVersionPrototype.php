<?php

namespace app\common\models;

/**
 * Prototype for a module version for any subsystem.
 */
abstract class SubsystemModuleVersionPrototype
{
    /** @var string Module ID */
    public string $moduleId;

    /** @var string[] List of modules this module depends on with version constraints */
    public array $dependencies = [];

    /** @var array List of module versions dependencies for other subsystems */
    public array $subsystemDependencies = [];

    /** @var string Module version */
    public string $version;

    /** @var string System version dependency */
    public string $systemVersion;

    /**
     * Returns ID of the subsystem this module is meant for.
     *
     * @return string
     */
    abstract public function getSubsystemID(): string;
}
