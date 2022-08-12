<?php

namespace app\infrastructure;

use app\core\spi\LoggerSPI;
use Yii;

/**
 * Logger implementation using Yii logger.
 */
class Logger implements LoggerSPI
{
    /**
     * @inheritDoc
     */
    public function info(string $message): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO);
    }
}
