<?php
function force_404($msg = null)
{
    if (env('developement')) {
        echo $msg;
    }
    render();
}
function render($path_to_include = "pages/404.php")
{
    ob_start();
    include_once $path_to_include;
    Page::getInstance()->setContent(ob_get_clean());
    require_root("template/layout.php");
    exit();
}
function route($route, $path_to_include)
{
    $_SESSION['current_route'] = $route;
    $callback = $path_to_include;
    if (!is_callable($callback)) {
        if (!strpos($path_to_include, '.php')) {
            $path_to_include .= '.php';
        }
    }
    if ($route == "/404") {
        render($path_to_include);
    }

    $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $request_url = rtrim($request_url, '/');
    $request_url = strtok($request_url, '?');
    $route_parts = explode('/', $route);
    $request_url_parts = explode('/', $request_url);
    array_shift($route_parts);
    array_shift($request_url_parts);
    if ($route_parts[0] == '' && count($request_url_parts) == 0) {
        render($path_to_include);
    }
    if (count($route_parts) != count($request_url_parts)) {
        return;
    }
    $parameters = [];
    foreach ($route_parts as $__i__ => $route_part) {
        if (preg_match("/^[$]/", $route_part)) {
            $route_part = ltrim($route_part, '$');
            array_push($parameters, $request_url_parts[$__i__]);
            global $$route_part;
            $$route_part = $request_url_parts[$__i__];
        } else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
            return;
        }
    }
    // Callback function
    if (is_callable($callback)) {
        call_user_func_array($callback, $parameters);
        exit;
    }
    render($path_to_include);
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
    if (empty($GLOBALS[$param])) {
        if ($strict) {
            force_404("Route parameter {$param} was not found");
        }
        return null;
    }
    if ($numeric and !is_numeric($GLOBALS[$param])) {
        force_404("Route parameter {$param} is not numeric");
    }
    return $GLOBALS[$param];
}
/** CSRF protection */
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