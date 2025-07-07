<?php

namespace App\Services;

use Monolog\Logger;

class AppLogger
{
    private static ?Logger $logger = null;

    public static function get(): Logger
    {
        if (!self::$logger) {
            self::$logger = LoggerFactory::create();
        }
        return self::$logger;
    }
}
