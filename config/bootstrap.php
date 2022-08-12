<?php


use app\core\api\HubAPI;
use app\core\services\HubControlService;
use app\core\spi\LoggerSPI;
use app\core\spi\SubsystemManagerAPI;
use app\infrastructure\Logger;
use app\infrastructure\SubsystemManager;


$di_map = [
    HubAPI::class => function () {
        $config = (include __DIR__ . '/subsystems.php') ?? [];
        $subsystems = array_map(function ($subsystemConfig) {
            return Yii::$container->get(SubsystemManagerAPI::class, [], $subsystemConfig);
        }, $config);

        return new HubControlService($subsystems);
    },
    SubsystemManagerAPI::class => SubsystemManager::class,
    LoggerSPI::class => Logger::class,
];

Yii::$container->setSingletons($di_map);
