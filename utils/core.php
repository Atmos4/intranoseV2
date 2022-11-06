<?php
$db_host = "localhost";
$db_name = "nose42_intra";
$db_user = "root";
$db_password = "";

global $database;
$database = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host . ";charset=UTF8", $db_user, $db_password);

// DB
/** Get global database */
function db()
{
    return $GLOBALS['database'];
}
/** perform a SQL query */
function query_db($sql_query, $args = null)
{
    if ($args) {
        $request = db()->prepare($sql_query);
        $request->execute($args);
        return $request;
    } else {
        return db()->query($sql_query);
    }
}
/** Calls query_db() and unwraps the result */
function fetch($sql, $args = null)
{
    return query_db($sql, $args)->fetchAll();
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
function page($page_title, $page_css = null, $with_nav = true, $page_description = null)
{
    global $title, $description, $css;
    $title = $page_title;
    $description = $page_description;
    $css = $page_css;
    if ($with_nav) {
        require_root("template/nav.php");
    }
}
/** Checks authentication and authorization stored in session */
function check_auth($level)
{
    if (!isset($_SESSION['user_permission'])) {
        redirect("login");
    }
    $ulevel = $_SESSION['user_permission'];
    if ($level != $ulevel) {
        return false;
    }
    return true;
}
