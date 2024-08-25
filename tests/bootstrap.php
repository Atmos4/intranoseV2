<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';
require_once __DIR__ . '/BaseTestCase.php';

session_start();

$testDb = BaseTestCase::getTestDBName();

// remove existing test DB
$existingDb = DBFactory::getSqliteLocation($testDb);
file_exists($existingDb) && unlink($existingDb);

$db = new DB(DBFactory::sqlite($testDb));

if (!SeedingService::applyMigrations($db)) {
    Cli::abort("There was a problem applying migrations");
}

$db->em()->getConnection()->close();