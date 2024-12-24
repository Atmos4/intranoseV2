<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';

$cli = new Cli;

foreach (ClubManagementService::listClubs() as $c) {
    $db = DB::forClub($c);
    if (!SeedingService::applyMigrations($db)) {
        $cli->error("Could not apply migrations to $c")->exit();
    } else
        $cli->ok("Migrated $c");
}

$cli->success();