<?php
$dotenv = Dotenv\Dotenv::createImmutable(base_path());
$dotenv->load();
$dotenv->required('DB_NAME');
$dotenv->required('DB_USER');
$dotenv->required('DB_PASSWORD');
$dotenv->ifPresent('DEVELOPMENT')->isBoolean();
$dotenv->required('BASE_URL');
$dotenv->required('MAIL_HOST');
$dotenv->required('MAIL_USER');
$dotenv->required('MAIL_PASSWORD');
return $dotenv; // if need to change things in specific execution envs.