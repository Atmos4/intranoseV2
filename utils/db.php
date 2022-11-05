<?php
$db_host = "localhost";
$db_name = "nose42_intra";
$db_user = "root";
$db_password = "";

global $database;
$database = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host . ";charset=UTF8", $db_user, $db_password);

session_start();

function db()
{
    return $GLOBALS['database'];
}

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

function fetch($sql, $args = null)
{
    return query_db($sql, $args)->fetchAll();
}
