<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';

foreach (ClubManagementService::listClubs() as $c) {
    $db = new DB(SqliteFactory::clubPath($c));
    if (!SeedingService::applyMigrations($db)) {
        echo Cli::error("Could not apply migrations to $c");
    } else
        echo Cli::ok("Migrated $c");
}

Cli::success();