<?php
use Doctrine\ORM\EntityManager;

// Project base path
define('BASE_PATH', dirname(__DIR__));

function base_path(): string
{
    return BASE_PATH;
}

function app_path(): string
{
    return BASE_PATH . "/app";
}

/** Returns an env variable */
function env(string $key)
{
    return Config::get($key);
}

/** Get global entity manager */
function em(): EntityManager
{
    return DB::get();
}

/** Get global formatter.
 * See {@see format_date()}
 * @param string|null $format When provided, sets custom global formatting, scoped to the entire script (the current page)
 * @return IntlDateFormatter The global formatter object
 */
function formatter(string $format = null): IntlDateFormatter
{
    return Formatter::get($format);
}

/**
 * Returns a named route parameter
 * @param mixed $param Route parameter
 * @param mixed $strict If the parameter is required
 * @param mixed $numeric If the parameter needs to be numeric, defaults to true
 * @return string|int|null
 */
function get_route_param($param, $strict = true, $numeric = true)
{
    return Router::getParameter($param, $strict, $numeric);
}

/** Redirect helper method */
function redirect($href)
{
    header("Location: " . $href);
    exit;
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
        Router::abort("Access for {$permission} is restricted for this page. ");
    }
}

function force_404($message = "")
{
    Router::abort($message);
}

function has_session($key): bool
{
    return isset($_SESSION[$key]);
}

function restrict_dev()
{
    if (!env('developement')) {
        force_404("Not in dev environement");
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
/** CSRF Protection */
function set_csrf()
{
    if (!isset($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(50));
    }
    return '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
}
function is_csrf_valid()
{
    if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) {
        return false;
    }
    if ($_SESSION['csrf'] != $_POST['csrf']) {
        return false;
    }
    return true;
}