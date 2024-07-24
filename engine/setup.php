<?php
// User control
require_once app_path() . "/components/user_control.php";
// Load env
$env = require_once __DIR__ . "/load_env.php";

$env->required('BASE_URL');
$env->required('MAIL_HOST');
$env->required('MAIL_USER');
$env->required('MAIL_PASSWORD');

// vite setup
if (is_dev()) {
    require_once __DIR__ . "/vite.php";
}

// static
$logger = new \Monolog\Logger('main');
if (is_dev()) {
    $logger->pushHandler(new \Monolog\Handler\BrowserConsoleHandler(\Monolog\Level::Debug));
}
$logger->pushHandler(new \Monolog\Handler\StreamHandler(base_path() . '/logs/app.log'));
$logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
$logger->pushProcessor(new \Monolog\Processor\WebProcessor());

MainLogger::instance(new MainLogger($logger));
OvhService::factory(fn() => new OvhService(ovh_api()));
Mailer::factory(fn() => env('DEVELOPMENT') && !env("USE_EMAIL_PRODUCTION") ? new MockMailer() : new Mailer());