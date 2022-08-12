<?php

namespace app\infrastructure\requests\subsystem;

class ModuleVersionsRequest extends SubsystemRequestPrototype
{
    /**
     * @inheritDoc
     */
    public function __construct(protected readonly string $moduleId, string $baseUrl)
    {
        parent::__construct($baseUrl);
    }

    /**
     * @inheritDoc
     */
    protected function getRelativePath(): string
    {
        return "/modules/$this->moduleId/versions";
    }
}
