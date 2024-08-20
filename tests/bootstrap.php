<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';
require_once __DIR__ . '/BaseTestCase.php';

DB::setupForTest();

// remove existing test DB
$existingDb = DBFactory::getSqliteLocation(DBFactory::getSqliteDbName());
file_exists($existingDb) && unlink($existingDb);

if (!SeedingService::applyMigrations()) {
    Cli::abort("There was a problem applying migrations");
}
echo Cli::ok("Migrations applied");