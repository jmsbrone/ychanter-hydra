<?php

namespace app\infrastructure\requests\subsystem;

class ModuleDeactivateRequest extends SubsystemRequestPrototype
{
    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        parent::init();
        $this->usePost();
    }

    /**
     * @inheritDoc
     */
    protected function getRelativePath(): string
    {
        return '/modules/deactivate';
    }
}
