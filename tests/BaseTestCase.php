<?php
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    private TestHandler $handler;
    private $tempDbFile;
    protected DB $db;
    protected Cli $cli;

    /** @inheritDoc */
    protected function setUp(): void
    {
        $cli = new Cli;
        $_SESSION = [];
        InstanceDependency::reset();
        SingletonDependency::reset();
        Toast::$toasts = [];
        $this->handler = new TestHandler;
        MainLogger::instance(new MainLogger(new Monolog\Logger("test", [$this->handler])));

        // create tests directory and copy temp db
        $testDir = SqliteFactory::mainPath('tests');
        !file_exists($testDir) && mkdir($testDir);
        $this->tempDbFile = "$testDir/" . $this::class . "." . $this->name() . ".sqlite";
        if (copy(SqliteFactory::mainPath(self::getTestDBName()), $this->tempDbFile)) // this will also delete the previous test data
        {
            $this->db = new DB($this->tempDbFile);
            // warning: remove this as singletons are not usable in phpunit
            DB::factory(fn() => $this->db);
        } else
            $cli->error("error creating test db");
    }

    /** @inheritDoc */
    protected function tearDown(): void
    {
        // Commenting this out for now. It's not useful to delete those replicates now. Maybe later when we have more tests
        //file_exists($this->tempDbFile) && unlink($this->tempDbFile);
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