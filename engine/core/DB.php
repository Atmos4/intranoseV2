<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\TokenType;


class DB extends SingletonDependency
{
    private EntityManager $entityManager;

    function __construct(Doctrine\DBAL\Connection $connection = null)
    {
        $evm = new \Doctrine\Common\EventManager;
        $tablePrefix = new \TablePrefix('orm_');
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

        $config = ORMSetup::createAttributeMetadataConfiguration(paths: array("database/models"), isDevMode: !!env("DEVELOPMENT"));
        $config->addCustomDatetimeFunction('MONTH', Month::class);
        $config->addCustomDatetimeFunction('DAY', Day::class);

        $connection ??= DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'user' => env("DB_USER"),
            'password' => env("DB_PASSWORD"),
            'dbname' => env("DB_NAME"),
            'host' => env("DB_HOST"),
            'charset' => 'utf8mb4',
        ], $config);

        $this->entityManager = new EntityManager($connection, $config, $evm);
    }

    static function get()
    {
        return self::getInstance()->entityManager;
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
