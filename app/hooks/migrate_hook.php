<?php
if (($_GET["token"] ?? null) != env("WEBHOOK_MIGRATION_TOKEN")) {
    echo "Invalid request";
    return;
}
echo "<b>Listing clubs</b><br>";
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
    echo "DB migrated successfully<br>";
    $clubs = ClubManagementService::listClubs();
    assert(count($clubs) == 1, "One club");
    assert($clubs[1] == $selectedSlug, "Only club should be the selected slug from env");
}

$saved_em = null;
foreach ($clubs as $c) {
    $db = DB::forClub($c);
    $saved_em ??= $db->em();
    if (!SeedingService::applyMigrations($db)) {
        echo "Could not apply migrations to $c<br>";
    } else
        echo "Migrated $c<br>";
}

// Generate proxies
if (!SeedingService::generateProxies($saved_em))
    echo "Could not generate proxies<br>";
else
    echo "Proxies generated<br>";

// --- one off migrations
if (env("SELECTED_CLUB")) {
    $slug = env("SELECTED_CLUB");
    if (!file_exists(club_data_path($slug))) {
        echo "Club directory not found";
        return;
    }

    function move_files($from, $to): string
    {
        if (!file_exists($from)) {
            return "$from not found<br>";
        }
        $files = scan_dir($from);
        if (!$files) {
            return "No files need to be moved<br>";
        }
        if (file_exists($to)) {
            return "Target dir already exists<br>";
        }
        rename($from, $to);
        return "Moved $from to $to<br>";
    }

    echo move_files(BASE_PATH . "/assets/images/profile", club_data_path(env("SELECTED_CLUB"), "uploads"));
    echo move_files(BASE_PATH . "/app/uploads", club_data_path(env("SELECTED_CLUB"), "profile"));
}

echo "Success :D<br>";