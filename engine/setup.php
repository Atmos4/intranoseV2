<?php
// User control
require_once app_path() . "/components/user_control.php";
// Load env
$env = require_once base_path() . "/engine/load_env.php";

$env->required('BASE_URL');
$env->required('MAIL_HOST');
$env->required('MAIL_USER');
$env->required('MAIL_PASSWORD');

// static
$logger = new \Monolog\Logger('main');
if (env('DEVELOPMENT')) {
    $logger->pushHandler(new \Monolog\Handler\BrowserConsoleHandler(\Monolog\Level::Debug));
}
$logger->pushHandler(new \Monolog\Handler\StreamHandler(base_path() . '/logs/app.log'));
$logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
$logger->pushProcessor(new \Monolog\Processor\WebProcessor());

MainLogger::instance(new MainLogger($logger));
Mailer::factory(fn() => env('DEVELOPMENT') && !env("EMAIL_MOCK_OFF") ? new MockMailer() : new Mailer());