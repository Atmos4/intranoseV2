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
            'user' => env("DB_USER"),
            'password' => env("DB_PASSWORD"),
            'dbname' => env("DB_NAME"),
            'host' => env("DB_HOST"),
            'charset' => 'utf8mb4'
        ], $config);
        $this->entityManager = new EntityManager($connection, $config);
    }

    static function get()
    {
        return self::getInstance()->entityManager;
    }
}