<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class IntraLogger extends Singleton
{
    private Logger $logger;

    protected function __construct()
    {
        $this->logger = new Logger('main');
        $this->logger->pushHandler(new StreamHandler(base_path() . '/logs/app.log'));
    }

    static function get()
    {
        return self::getInstance()->logger;
    }
}