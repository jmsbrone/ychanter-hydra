<?php

namespace app\common\models;

use app\common\traits\GenericModuleInfo;
use yii\base\Model;

/**
 * Module information entity.
 */
class SubsystemModule extends Model
{
    use GenericModuleInfo;

    /** @var bool Whether the module is installed in the subsystem */
    public bool $installed = false;
    /** @var bool Whether the module is active in the subsystem */
    public bool $active = false;
    /** @var string|null If module is installed must contain the installed version */
    public ?string $version = null;
    /** @var string ID of the subsystem this module belongs to */
    public string $subsystemId;
}
