<?php

namespace app\common\models;

use yii\base\BaseObject;

/**
 * Prototype for a module version for any subsystem.
 */
class SubsystemModuleVersion extends BaseObject
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
}
