<?php

namespace app\core;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

/**
 * Manager for core ports.
 * Provides a way to access infrastructure ports without
 * passing them with Dependency Injector.
 */
class PortManager
{
    /**
     * Returns instance of the port by its class.
     *
     * @template PortType
     * @param PortType $class Port class to retrieve
     * @param bool $optional Whether the method should return null if the port does not exist.
     * Exception will be thrown otherwise.
     * @return PortType|null
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public static function getPort(string $class, bool $optional = false): ?object
    {
        try {
            return Yii::$container->get($class);
        } catch (Exception $e) {
            if ($optional) {
                return null;
            }

            throw $e;
        }
    }
}
