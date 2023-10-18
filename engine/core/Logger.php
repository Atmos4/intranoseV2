<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log extends Singleton
{
    private Logger $logger;

    protected function __construct()
    {
        $this->logger = new Logger('main');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));
    }

    static function get()
    {
        return self::getInstance()->logger;
    }
}