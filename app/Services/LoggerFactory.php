<?php

namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerFactory
{
    public static function create(): Logger
    {
        $logEnabled = filter_var($_ENV['ENABLE_LOGS'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $logLevel = (int)($_ENV['LOGLEVEL'] ?: 100);
        $logDir = $_ENV['LOGDIR'] ?: 'logs';

        $logPath = __DIR__ . '/../../' . $logDir . '/app.log';

        $logger = new Logger('support-time');

        if ($logEnabled) {
            $logger->pushHandler(new StreamHandler($logPath, $logLevel));
        }

        return $logger;
    }
}