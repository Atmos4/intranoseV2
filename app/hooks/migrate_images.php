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

$clubs = ClubManagementService::listClubs();
foreach ($clubs as $c) {
    echo "Processing club $c... $br";
    $db = DB::forClub($c);

    $users = UserService::getAll($db);
    foreach ($users as $u) {
        if (str_contains($u->picture, "assets/images")) {
            echo "Replacing picture for $u->login $br";
            $parts = explode("/", $u->picture);
            $pp = array_pop($parts);
            $u->picture = ".club_data/$c/profile/$pp";
        }
        $db->em()->flush();
    }
    echo "Done for club $c $br";
}

echo "Migration ok";