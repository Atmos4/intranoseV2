<?php

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;

class MainLogger extends StaticInstance
{
    private Logger $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    static function get()
    {
        return self::getInstance()->logger;
    }
}