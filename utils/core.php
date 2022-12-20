<?php
$db_host = "localhost";
$db_name = "nose42_intra";
$db_user = "root";
$db_password = "";

global $database, $formatter;
$database = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host . ";charset=UTF8", $db_user, $db_password);
$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, "Europe/Paris");

/** Get global database */
function db(): PDO
{
    return $GLOBALS['database'];
}
/** Get global formatter */
function formatter(): IntlDateFormatter
{
    return $GLOBALS['formatter'];
}
/** perform a SQL query
 * @return PDOStatement|false
 */
function query_db($sql_query, ...$args)
{
    if ($args) {
        $request = db()->prepare($sql_query);
        $request->execute($args);
        return $request;
    } else {
        return db()->query($sql_query);
    }
}
/** Calls query_db() and unwraps the result.
 * @return array a list of DB entities
 */
function fetch($sql, ...$args)
{
    return query_db($sql, ...$args)->fetchAll();
}
/** Calls fetch() and throws if there isn't a single element.
 * @return array a single DB entity.
 */
function fetch_single($sql, ...$args)
{
    $result = fetch($sql, ...$args);
    if (count($result) != 1) {
        force_404();
    }
    return $result[0];
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
/** Setup page, initializes a bunch of globals */
function page(string $page_title, string $page_css = null, bool $with_nav = true, string $page_display_title = null, string $page_description = null)
{
    global $title, $description, $css, $nav, $display_title;
    $title = $page_title;
    $description = $page_description;
    $nav = $with_nav;
    $display_title = $page_display_title;
    $css = "/assets/css/" . $page_css;
}
/** Checks authentication and authorization stored in session */
function check_auth(...$levels)
{
    if (!isset($_SESSION['user_permission'])) {
        redirect("login");
    }
    $ulevel = $_SESSION['user_permission'];
    foreach ($levels as $level) {
        if ($level != $ulevel) {
            return false;
        }
    }
    return true;
}

/** Restrict access to authenticated users, and to a set of authorized users if provided arguments.
 * Should be used as early as possible, to prevent unnecessary data loading.
 */
function restrict_access(...$permissions)
{
    if (!isset($_SESSION['user_permission']) || (count($permissions) && !in_array($_SESSION['user_permission'], $permissions))) {
        force_404();
    }
}

/**
 * Format a date.
 * @param string|int|DateTime $date The date either as a string, a timestamp or a DateTime.
 * @return string The date formatted
 */
function format_date($date)
{
    if (gettype($date) === "string") {
        $date = date_create($date);
    }
    return formatter()->format($date);
}
