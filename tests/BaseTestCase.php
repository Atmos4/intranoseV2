<?php
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    private TestHandler $handler;
    private $tempDbFile;
    protected DB $db;

    /** @inheritDoc */
    protected function setUp(): void
    {
        $_SESSION = [];
        InstanceDependency::reset();
        SingletonDependency::reset();
        Toast::$toasts = [];
        $this->handler = new TestHandler;
        MainLogger::instance(new MainLogger(new Monolog\Logger("test", [$this->handler])));

        // create tests directory and copy temp db
        !file_exists(DBFactory::getSqliteLocation('tests')) && mkdir(DBFactory::getSqliteLocation('tests'));
        $this->tempDbFile = 'tests/' . $this::class . "." . $this->name() . ".sqlite";
        if (copy(DBFactory::getSqliteLocation(self::getTestDBName()), DBFactory::getSqliteLocation($this->tempDbFile))) // this will also delete the previous test data
        {
            $this->db = new DB(DBFactory::sqlite($this->tempDbFile));
            // warning: remove this as singletons are not usable in phpunit
            DB::factory(fn() => $this->db);
        } else
            echo Cli::error("error creating test db");
    }

    /** @inheritDoc */
    protected function tearDown(): void
    {
        // Commenting this out for now. It's not useful to delete those replicates now. Maybe later when we have more tests
        //file_exists(DBFactory::getSqliteLocation($this->tempDbFile)) && unlink(DBFactory::getSqliteLocation($this->tempDbFile));
    }

    /** @return Monolog\LogRecord[] */
    protected function getLogs(): array
    {
        return $this->handler->getRecords();
    }

    public static function getTestDBName()
    {
        return env("TEST_DB_NAME") ?? 'db_test.sqlite';
    }
}