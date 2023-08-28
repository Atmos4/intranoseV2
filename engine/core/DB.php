<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class DB extends Singleton
{
    private EntityManager $entityManager;

    protected function __construct()
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(paths: array("database/models"), isDevMode: true);
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'user' => env("db_user"),
            'password' => env("db_password"),
            'dbname' => env("db_name"),
            'host' => env("db_host"),
            'charset' => 'utf8mb4'
        ], $config);
        $this->entityManager = new EntityManager($connection, $config);
    }

    static function get()
    {
        return self::getInstance()->entityManager;
    }
}