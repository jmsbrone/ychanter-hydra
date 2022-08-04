<?php

use app\core\api\ServerAPI;
use app\core\services\ServerModuleService;

$di_map = [
    ServerAPI::class => ServerModuleService::class,
];

Yii::$container->setSingletons($di_map);
