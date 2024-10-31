<?php
// User control
require_once app_path() . "/components/user_control.php";
// Load env
$env = require_once __DIR__ . "/load_env.php";

// static
$logger = new \Monolog\Logger('main');
if (is_dev()) {
    $logger->pushHandler(new \Monolog\Handler\BrowserConsoleHandler(\Monolog\Level::Debug));
}
$logger->pushHandler(new \Monolog\Handler\StreamHandler(base_path() . '/logs/app.log'));
$logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
$logger->pushProcessor(new \Monolog\Processor\WebProcessor());

DB::setupForApp(!env("DB_HOST"));
MainLogger::instance(new MainLogger($logger));
Mailer::factory(fn() => (is_dev() && !env("EMAIL_MOCK_OFF")) || env("EMAIL_MOCK") ? new MockMailer() : new Mailer());

//services DI
AuthService::factory(fn() => new AuthService(em()));
