<?php

namespace app\infrastructure\requests\subsystem;

use app\infrastructure\requests\CurlRequest;

/**
 * Prototype request description.
 */
abstract class SubsystemRequestPrototype extends CurlRequest
{
    /**
     * @inheritDoc
     */
    public function __construct(string $baseUrl)
    {
        parent::__construct($baseUrl . '/hydra' . $this->getRelativePath());
        $this->setHeader('Content-Type', 'application/json');
    }

    /**
     * Returns relative path of Hydra request (relative to /hydra path).
     * For example, for '/hydra/modules' must return '/modules'.
     *
     * @return string
     */
    abstract protected function getRelativePath(): string;

    /**
     * Sets Bearer token for requests
     *
     * @param string $token Authorization token
     * @return void
     */
    public function setBearerToken(string $token): void
    {
        $this->setHeader('Authorization', "Bearer $token");
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        parent::init();
        $this->setHeader('Content-Type', 'application/json');
    }

    /**
     * @inheritDoc
     */
    protected function processResponseData(bool|string $result): mixed
    {
        return json_decode($result, true);
    }
}
