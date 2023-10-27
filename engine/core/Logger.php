<?php

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;

class IntraLogger extends Singleton
{
    private Logger $logger;

    protected function __construct()
    {
        $this->logger = new Logger('main');
        if (env('DEVELOPMENT')) {
            $this->logger->pushHandler(new BrowserConsoleHandler(Level::Debug));
        }
        $this->logger->pushHandler(new StreamHandler(base_path() . '/logs/app.log'));

        $this->logger->pushProcessor(new PsrLogMessageProcessor());
        $this->logger->pushProcessor(new WebProcessor());
    }

    static function get()
    {
        return self::getInstance()->logger;
    }
}