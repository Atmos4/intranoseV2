<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Proxy\Autoloader;
use Doctrine\ORM\Query\TokenType;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class DB extends SingletonDependency
{
    private const PATH_PROXIES = "database/proxies";

    private EntityManager $entityManager;

    public string|false $sqlite;

    function isSqlite()
    {
        return !!$this->sqlite;
    }
    function em()
    {
        return $this->entityManager;
    }

    function __construct(Connection $connection)
    {
        $this->sqlite = $connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDO\SQLite\Driver;
        if ($this->sqlite) {
            $connection->executeQuery("PRAGMA journal_mode = WAL;"); // speeds up sqlite
        }

        // ORM Tables prefix
        $evm = new \Doctrine\Common\EventManager;
        $tablePrefix = new \TablePrefix('orm_');
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

        $devMode = !!env("DEVELOPMENT");

        if ($devMode) {
            $queryCache = new ArrayAdapter();
            $metadataCache = new ArrayAdapter();
        } else {
            $queryCache = new PhpFilesAdapter('doctrine_queries');
            $metadataCache = new PhpFilesAdapter('doctrine_metadata');
        }

        $config = ORMSetup::createAttributeMetadataConfiguration(paths: array("database/models"), isDevMode: $devMode, proxyDir: self::PATH_PROXIES);
        $config->setMetadataCache($metadataCache);
        $config->setQueryCache($queryCache);

        if ($this->sqlite) {
            $config->addCustomDatetimeFunction('MONTH', Month_Sqlite::class);
            $config->addCustomDatetimeFunction('DAY', Day_Sqlite::class);
        } else {
            $config->addCustomDatetimeFunction('MONTH', Month::class);
            $config->addCustomDatetimeFunction('DAY', Day::class);
        }


        if ($devMode) {
            $config->setAutoGenerateProxyClasses(true);
        } else {
            $config->setAutoGenerateProxyClasses(false);
            Autoloader::register(self::PATH_PROXIES, "");
        }

        $this->entityManager = new EntityManager($connection, $config, $evm);
    }

    static function get()
    {
        return self::getInstance()->em();
    }

    static function setupForApp($sqlite)
    {
        self::factory(fn() => new self($sqlite ? DBFactory::sqlite() : DBFactory::mysql()));
    }

    static function setupForTest($dbName)
    {
        self::factory(fn() => new self(DBFactory::sqlite($dbName)));
    }
}

class DBFactory
{
    static function mysql($dbName = null)
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'user' => env("DB_USER"),
            'password' => env("DB_PASSWORD"),
            'dbname' => $dbName ?? env("DB_NAME"),
            'host' => env("DB_HOST"),
            'charset' => 'utf8mb4',
        ]);
    }

    static function sqlite($fileName = null)
    {
        self::createSqliteDirIfNotExists();
        return DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => self::getSqliteLocation($fileName ?? self::getSqliteDbName())]);
    }

    static function createSqliteDirIfNotExists()
    {
        $sqliteDir = __DIR__ . "/../../.sqlite";
        if (!file_exists($sqliteDir)) {
            mkdir($sqliteDir);
        }
    }

    // Configuration factory
    static function getConfig($db)
    {
        return !!$db->isSqlite() ?
            new PhpFile(__DIR__ . "/../../database/config/sqlite.php") :
            new PhpFile(__DIR__ . "/../../database/config/migrations.php");
    }

    static function getSqliteDbName($file = null)
    {
        return $file ?? env("SQLITE_DB_NAME") ?? 'db.sqlite';
    }

    static function getSqliteLocation($file)
    {
        return __DIR__ . "/../../.sqlite/$file";
    }
}

/**
 * @author Rafael Kassner <kassner@gmail.com>
 * @author Sarjono Mukti Aji <me@simukti.net>
 */
class Month extends FunctionNode
{
    public $date;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'MONTH(' . $sqlWalker->walkArithmeticPrimary($this->date) . ')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}

class Month_Sqlite extends FunctionNode
{
    public $date;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return "strftime('%m', " . $sqlWalker->walkArithmeticPrimary($this->date) . ')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}

class Day extends FunctionNode
{
    public $date;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'DAY(' . $sqlWalker->walkArithmeticPrimary($this->date) . ')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}



class Day_Sqlite extends FunctionNode
{
    public $date;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return "strftime('%d', " . $sqlWalker->walkArithmeticPrimary($this->date) . ')';
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}

class TablePrefix
{
    protected $prefix = '';

    public function __construct($prefix)
    {
        $this->prefix = (string) $prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix . $classMetadata->getTableName()
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
            }
        }
    }
}
