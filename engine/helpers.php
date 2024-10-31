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

/** readline polyfill because Linux sucks balls */
if (!function_exists("readline")) {
    function readline($prompt = null)
    {
        if ($prompt) {
            echo $prompt;
        }
        $fp = fopen("php://stdin", "r");
        $line = rtrim(fgets($fp, 1024));
        return $line;
    }
}

function component(string $location): Component
{
    return new Component($location);
}

/** Returns an env variable */
function env(string $key)
{
    return $_ENV[$key] ?? null;
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
function get_route_param($param, $strict = true, $numeric = true, $pattern = null)
{
    return Router::getParameter($param, $strict, $numeric, $pattern);
}

// TODO: move to router
function get_query_param($param, $strict = false, $numeric = true, $pattern = null)
{
    $query_param = $_GET[$param] ?? null;
    if (!$query_param) {
        if ($strict) {
            Router::abort(message: "Query parameter $param was not found");
        }
        return null;
    }
    if (!$pattern and $numeric and !is_numeric($query_param)) {
        Router::abort(message: "Query parameter $param is not numeric");
    }
    if ($pattern and !preg_match($pattern, $query_param)) {
        Router::abort(message: "Route parameter $param doesn't match pattern $pattern");
    }
    return $query_param;
}

/**
 * Returns the value of a request header
 * @param string $headerName 
 * @return string header value 
 */
function get_header($headerName): string|null
{
    return $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $headerName))] ?? null;
}

/** Redirect helper method */
function redirect($href)
{
    Toast::stash();
    if (get_header("HX-Request")) {
        header("HX-Location: $href");
    } else {
        header("Location: $href");
    }
    exit;
}

/** escape characters */
function e($s)
{
    return htmlspecialchars($s ?? "");
}

/** Setup page */
function page(string $title)
{
    return Page::getInstance()->title($title);
}

/** setup page actions */
function actions($condition = true)
{
    return $condition ? new PageActionBuilder() : null;
}

/** Checks authentication and authorization stored in session
 * @param Permission[] $levels
 */
function check_auth($levels = [])
{
    if (!AuthService::create()->isUserLoggedIn()) {
        redirect("/");
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
function restrict_access($permissions = [], $redirect = true)
{

    if (!AuthService::create()->isUserLoggedIn()) {
        if ($redirect) {
            $_SESSION["deep_url"] = $_SERVER['REQUEST_URI'];
            redirect("/");
        }
        exit;
    }
    if (!isset($_SESSION['user_permission']) || (count($permissions) && !in_array($_SESSION['user_permission'], $permissions))) {
        $permission = $_SESSION['user_permission']?->value ?? "non authenticated user";
        if (get_header('HX-Request')) {
            // This is a bit lazy but it's the idea
            header('HX-Refresh: true');
        }
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

function is_dev()
{
    return !env('PRODUCTION');
}

function restrict_dev()
{
    if (!is_dev()) {
        force_404("Not in dev environement");
    }
}

function restrict_environment($key)
{
    if (!is_dev() && !env($key)) {
        force_404();
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

// Logger
function logger()
{
    return MainLogger::get();
}