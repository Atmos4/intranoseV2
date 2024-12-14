<?php
// create empty .env file if not exist
if (!file_exists(base_path() . "/.env")) {
    fopen(".env", "");
}

$dotenv = Dotenv\Dotenv::createImmutable(base_path());
$dotenv->load();

$dotenv->required("MGMT_PASSWORD");

// TODO - this is temporary. remove when proper club selection logic is in place.
if (env("PRODUCTION")) {
    $dotenv->required("SELECTED_CLUB");
    $dotenv->required("WEBHOOK_MIGRATION_TOKEN");
}

// Mailing - unused at the moment
// AP 2024-07 - TODO FIXME mailing does not work, we need to plug it into Gmail
$dotenv->ifPresent('USE_DKIM')->isBoolean();
if (env("USE_DKIM")) {
    $dotenv->required("DKIM_DOMAIN");
    $dotenv->required("DKIM_SELECTOR");
    $dotenv->required("DKIM_PASSPHRASE");
    $dotenv->required("DKIM_FILENAME");
}
return $dotenv; // if need to change things in specific execution envs.