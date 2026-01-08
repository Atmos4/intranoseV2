<?php
$validToken = env("WEBHOOK_MIGRATION_TOKEN");
$tryToken = $_GET["token"] ?? null;
$br = "<br>" . PHP_EOL;

// Check if the client sent credentials
if (!$validToken) {
    echo "<b>WARNING: no token defined. Make sure this isn't production</b>$br{$br}";
}

if ($tryToken != $validToken) {
    echo "Invalid request";
    return;
}
echo "<b>Listing clubs</b>{$br}";
$clubs = ClubManagementService::listClubs();

$selectedSlug = env("SELECTED_CLUB");

if (!$clubs) {
    if (!$selectedSlug) {
        echo "No club found";
        return;
    }

    mk_dir(club_data_path($selectedSlug), true);

    // Else we want to migrate DB
    $mainDbPath = SqliteFactory::mainPath(env("SQLITE_DB_NAME") ?? "db.sqlite");
    $clubDbPath = SqliteFactory::clubPath(env("SELECTED_CLUB"));
    if (!file_exists($mainDbPath)) {
        echo "Source db not found";
        return;
    }

    if (!copy($mainDbPath, $clubDbPath)) {
        echo "Copying db failed";
        return;
    }
    echo "DB migrated successfully{$br}";
    $clubs = ClubManagementService::listClubs();
    assert(count($clubs) == 1, "One club");
    assert($clubs[1] == $selectedSlug, "Only club should be the selected slug from env");
}

$saved_em = null;
foreach ($clubs as $c) {
    $db = DB::forClub($c);
    $saved_em ??= $db->em();
    if (!SeedingService::applyMigrations($db)) {
        echo "Could not apply migrations to $c{$br}";
    } else
        echo "Migrated $c{$br}";
}

// Generate proxies
if (env("DOCTRINE_DISABLE_PROXIES")) {
    echo "Proxy generation is disabled, skipping...";
} elseif (!SeedingService::generateProxies($saved_em)) {
    echo "Could not generate proxies{$br}";
} else {
    echo "Proxies generated{$br}";
}

// --- one off migrations
if (env("SELECTED_CLUB")) {
    echo "{$br}<b>Migrating directories...</b>{$br}";
    $slug = env("SELECTED_CLUB");
    if (!file_exists(club_data_path($slug))) {
        echo "Club directory not found";
        return;
    }

    $paths = [
        [BASE_PATH . "/assets/images/profile", club_data_path(env("SELECTED_CLUB"), "uploads")],
        [BASE_PATH . "/app/uploads", club_data_path(env("SELECTED_CLUB"), "profile")]
    ];


    foreach ($paths as $p) {
        [$from, $to] = $p;
        if (!file_exists($from)) {
            echo "$from not found{$br}";
            continue;
        }
        $files = scan_dir($from);
        if (!$files) {
            echo "No files need to be moved{$br}";
            continue;
        }
        if (file_exists($to)) {
            echo "Target dir already exists{$br}";
            continue;
        }
        mk_dir($to, true);
        rename($from, $to);
        echo "Moved $from to $to{$br}";
    }
}

echo "{$br}{$br}Success :D{$br}";
