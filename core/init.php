<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once "vendor/autoload.php";

session_start();

require_once "core/classes.php";
require_once "core/user_control.php";

/** Returns an env variable */
function env(string $key)
{
    return Env::getInstance()->getValue($key);
}

$config = ORMSetup::createAttributeMetadataConfiguration(paths: array("database/models"), isDevMode: true);

// TODO: temporary strings here for ORM connection. Replace with proper ones.
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'user' => env("db_user"),
    'password' => env("db_password"),
    'dbname' => env("db_name"),
    'host' => env("db_host"),
    'charset' => 'utf8mb4'
], $config);

global $database, $formatter, $entityManager;
$entityManager = new EntityManager($connection, $config);
$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, "Europe/Paris");

// Time zone and locale
date_default_timezone_set("Europe/Paris");
setlocale(LC_ALL, "French");
//ini_set('display_errors', 1);

function em(): EntityManager
{
    return $GLOBALS['entityManager'];
}

/** Get global formatter.
 * See {@see format_date()}
 * @param string|null $format When provided, sets custom global formatting, scoped to the entire script (the current page)
 * @return IntlDateFormatter The global formatter object
 */
function formatter(string $format = null): IntlDateFormatter
{
    global $formatter;
    if ($format) {
        $formatter->setPattern($format);
    }
    return $formatter;
}

// HELPER METHODS
/** Redirect helper method */
function redirect($href)
{
    header("Location: " . $href);
    exit;
}
/** Require from root folder */
function require_root($path)
{
    require_once $_SERVER['DOCUMENT_ROOT'] . "/" . $path;
}
/** Setup page */
function page(string $title)
{
    return Page::getInstance()->title($title);
}

/** Checks authentication and authorization stored in session
 * @param Permission[] $levels
 */
function check_auth($levels = [])
{
    if (!isset($_SESSION['user_permission'])) {
        redirect("login");
    }
    $ulevel = $_SESSION['user_permission'];
    foreach ($levels as $level) {
        if ($level == $ulevel) {
            return true;
        }
    }
    return false;
}

/** Restrict access to authenticated users, and to a set of authorized users if provided arguments.
 * Should be used as early as possible, to prevent unnecessary data loading.
 * @param Permission[] $permissions
 */
function restrict_access($permissions = [])
{
    if (!isset($_SESSION['user_permission']) || (count($permissions) && !in_array($_SESSION['user_permission'], $permissions))) {
        $permission = $_SESSION['user_permission']?->value ?? "non authenticated user";
        force_404("Access for {$permission} is restricted for this page. ");
    }
    if (!em()->find(User::class, $_SESSION['user_id'])->active) {
        force_404("Votre compte est inactif.");
    }
}

/**
 * Format a date.
 * @param string|int|DateTime $date The date either as a string, a timestamp or a DateTime.
 * @return string The date formatted
 */
function format_date($date, string $format = null)
{
    if (gettype($date) === "string") {
        $date = date_create($date);
    }
    return formatter($format)->format($date);
}