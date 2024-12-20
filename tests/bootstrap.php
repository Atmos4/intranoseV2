<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';
require_once __DIR__ . '/BaseTestCase.php';

session_start();

// remove existing test DB
$existingDb = SqliteFactory::mainPath(BaseTestCase::getTestDBName());
file_exists($existingDb) && unlink($existingDb);

$db = DB::forTest($existingDb);

if (!SeedingService::applyMigrations($db)) {
    (new Cli)->error("There was a problem applying migrations")->exit();
}

$db->em()->getConnection()->close();