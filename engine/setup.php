<?php
// User control
require_once app_path() . "/components/user_control.php";
// Load env
require_once base_path() . "/engine/load_env.php";

// static
$logger = new \Monolog\Logger('main');
if (env('DEVELOPMENT')) {
    $logger->pushHandler(new \Monolog\Handler\BrowserConsoleHandler(\Monolog\Level::Debug));
}
$logger->pushHandler(new \Monolog\Handler\StreamHandler(base_path() . '/logs/app.log'));
$logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
$logger->pushProcessor(new \Monolog\Processor\WebProcessor());
MainLogger::init(new MainLogger($logger));

// dependency injection
OvhService::setFactory(fn() => new OvhService(ovh_api()));
Mailer::setFactory(fn() => env('DEVELOPMENT') ? new MockMailer() : new Mailer());