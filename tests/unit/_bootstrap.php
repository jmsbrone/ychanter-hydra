<?php

// add unit testing specific bootstrap code here

use app\core\spi\LoggerSPI;
use app\infrastructure\Logger;

Yii::$container->setSingletons([
    LoggerSPI::class => Logger::class,
]);
