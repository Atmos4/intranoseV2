<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class DB extends Singleton
{
    private EntityManager $entityManager;

    protected function __construct()
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(paths: array("database/models"), isDevMode: true);
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'user' => env("DB_USER"),
            'password' => env("DB_PASSWORD"),
            'dbname' => env("DB_NAME"),
            'host' => env("DB_HOST"),
            'charset' => 'utf8mb4'
        ], $config);
        $config->addCustomDatetimeFunction('MONTH', Month::class);
        $config->addCustomDatetimeFunction('DAY', Day::class);
        $this->entityManager = new EntityManager($connection, $config);
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
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
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
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}