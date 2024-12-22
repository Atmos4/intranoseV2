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

    function isSqlite()
    {
        return true; //!!$this->sqlite; Uncomment if we switch back to another system
    }
    function em()
    {
        return $this->entityManager;
    }

    /**
     * The DB constructor should be private. If there are more use cases you need to cover, create a factory function
     */
    private function __construct(public string|null $sqlitePath = null, public Connection|null $connection = null)
    {
        if (!$sqlitePath && !$connection) {
            throw new Error("Unable to create DB connection");
        }
        $connection ??= DBFactory::sqlite($sqlitePath);
        if ($this->sqlitePath) {
            $connection->executeQuery("PRAGMA journal_mode = WAL;"); // speeds up sqlite
        }

        // ORM Tables prefix
        $evm = new \Doctrine\Common\EventManager;
        $tablePrefix = new \TablePrefix('orm_');
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

        $devMode = !!is_dev();

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

        if ($this->sqlitePath) {
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

    function path()
    {
        return dirname($this->sqlitePath);
    }

    static function get()
    {
        return self::getInstance()->em();
    }

    static function forClub($slug)
    {
        return new DB(SqliteFactory::clubPath($slug));
    }
    static function forTest($path)
    {
        return new DB($path);
    }

    static function setupForClub($slug)
    {
        assert(!!$slug, "Club namespace should be defined"); // TODO - refactor this in the future
        self::factory(fn() => self::forClub($slug));
    }
}

class DBFactory
{
    /**
     * @deprecated We use SQLite now
     */
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

    static function sqlite($fileName)
    {
        mk_dir(dirname($fileName));
        return DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => $fileName]);
    }
    // Configuration factory
    static function getConfig(DB $db)
    {
        return !!$db->isSqlite() ?
            new PhpFile(__DIR__ . "/../../database/config/sqlite.php") :
            new PhpFile(__DIR__ . "/../../database/config/migrations.php");
    }
}

class SqliteFactory
{
    static function clubPath($slug)
    {
        return club_data_path($slug) . "/db.sqlite";
    }
    static function mainPath($file = 'db.sqlite')
    {
        return base_path() . "/.sqlite/$file";
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
