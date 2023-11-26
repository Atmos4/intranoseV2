<?php
use Monolog\Logger;

class MainLogger extends InstanceDependency
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