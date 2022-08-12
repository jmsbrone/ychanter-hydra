<?php

include_once __DIR__ . '/bootstrap.php';

$config = require __DIR__ . '/common.php';
$config = array_merge_recursive($config, [
    'id' => 'basic-console',
    'controllerNamespace' => 'app\commands',
]);

return $config;
