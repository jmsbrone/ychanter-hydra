<?php

include_once __DIR__ . '/bootstrap.php';

$config = [
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'hades_redis_1',
                'port' => 6379,
                'database' => 2,
            ],
            'defaultDuration' => 86400,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/info.log',
                ],
            ],
        ],
    ],
    'params' => [
        'version' => require __DIR__ . '/version.php',
    ]
];

return $config;
