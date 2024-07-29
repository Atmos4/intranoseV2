<?php
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    private TestHandler $handler;

    /** @inheritDoc */
    protected function setUp(): void
    {
        InstanceDependency::reset();
        Toast::$toasts = [];
        $this->handler = new TestHandler;
        DB::setupForTest();
        MainLogger::instance(new MainLogger(new Monolog\Logger("test", [$this->handler])));
    }

    /** @return Monolog\LogRecord[] */
    protected function getLogs(): array
    {
        return $this->handler->getRecords();
    }
}