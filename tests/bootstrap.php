<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';
require_once __DIR__ . '/BaseTestCase.php';

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;

DBFactory::mysql(env("TEST_DB_NAME") ?? 'intranose_test');
$configFile = DBFactory::getMySqlConfig();

$dependencyFactory = DependencyFactory::fromConnection(
    $configFile,
    new ExistingConnection($connection),
);

$migrateCommand = new MigrateCommand($dependencyFactory);

$input = new ArrayInput([]);
$input->setInteractive(false);
$output = new BufferedOutput();

// unicode magic
$check = "\033[32m\u{2713}\033[0m";

// Create db if not exists
if (!env("SKIP_TEST_DB_CREATE")) {
    $createDatabasePdo = new PDO(
        "mysql:host:" . env("DB_HOST"),
        env("DB_USER"),
        env("DB_PASSWORD"),
    );
    $result = $createDatabasePdo->query("CREATE DATABASE IF NOT EXISTS $testDbName DEFAULT CHARACTER SET utf8mb4")->fetchAll();
    echo "$check Test database created" . PHP_EOL;
}

$exitcode = $migrateCommand->run($input, $output);
if ($exitcode === 0) {
    echo "$check Migrations applied" . PHP_EOL;
}