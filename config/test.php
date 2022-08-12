<?php

$params = include __DIR__ . '/params-test.php' ?? [];

$config = [
    'id' => 'test-app',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/test.log',
                ],
            ],
        ],
    ],
    'params' => array_merge($params, [
        'version' => require __DIR__ . '/version.php',
    ]),
];

return $config;
