<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';

use Doctrine\DBAL\DriverManager;

$db = env("TEST_DB_NAME") ?? 'intranose_test';
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'user' => env("DB_USER"),
    'password' => env("DB_PASSWORD"),
    'dbname' => $db,
    'host' => env("DB_HOST"),
    'port' => env("DB_PORT") ?? "3306",
    'charset' => 'utf8mb4',
]);

print_r($connection->executeQuery("SHOW CREATE DATABASE $db")->fetchAllAssociative());