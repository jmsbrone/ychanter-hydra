<?php

include_once __DIR__ . '/bootstrap.php';

$localParams = include __DIR__ . '/params-' . YII_ENV . '.php' ?? [];
$dbParams = require __DIR__ . '/db.php';

$config = [
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'auth'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'defaultDuration' => 3600,
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
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=db;port=5432;dbname=' . $dbParams['database'],
            'username' => $dbParams['user'],
            'password' => $dbParams['password'],
            'charset' => 'utf8',
            'tablePrefix' => 'yc_',
            'attributes' => [
                PDO::ATTR_PERSISTENT => YII_ENV_PROD,
            ],
            'enableSchemaCache' => YII_ENV_PROD,
            'schemaCacheDuration' => 60,
        ]
    ],
    'modules' => [
        'auth' => [
            'class' => 'app\modules\auth\Module',
        ]
    ],
    'params' => array_merge([
        'version' => require __DIR__ . '/version.php',
        'jwt' => [
            'algorithm' => 'HS256',
        ],
    ], $localParams)
];

return $config;
