<?php

namespace app\common\traits;

/**
 * Contains generic fields for classes providing information about a module.
 */
trait GenericModuleInfo
{
    /** @var string Module ID */
    public string $moduleId;

    /** @var string Module's vendor name */
    public string $vendor;

    /** @var string Module name */
    public string $name;

    /** @var string Title of the module for display */
    public string $title;

    /** @var string Description of the module */
    public string $description;
}
