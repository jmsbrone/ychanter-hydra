<?php

namespace app\infrastructure\requests\subsystem;

class ModuleListRequest extends SubsystemRequestPrototype
{
    public function filterByInstalled(): static
    {
        $this->data = [
            'filter' => [
                'installed' => true,
            ]
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getRelativePath(): string
    {
        return '/modules';
    }
}
