<?php
$dotenv = Dotenv\Dotenv::createImmutable(base_path());
$dotenv->load();

// Database
if (env("DB_HOST")) {
    $dotenv->required('DB_NAME');
    $dotenv->required('DB_USER');
    $dotenv->required('DB_PASSWORD');
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