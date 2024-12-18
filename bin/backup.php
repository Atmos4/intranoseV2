<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/load_env.php';

foreach (ClubManagementService::listClubs() as $c) {
    $service = new BackupService(true, SqliteFactory::clubPath($c));
    $service->createBackup();
}