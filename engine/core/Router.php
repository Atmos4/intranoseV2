<?php
class Router extends Singleton
{

    private array $dynamicSegments;

    private function render($path_to_include = "pages/404.php")
    {
        ob_start();
        include_once app_path() . "/$path_to_include";
        Page::getInstance()->setContent(ob_get_clean());
        require_once app_path() . "/template/layout.php";
        exit();
    }

    static function abort(string $message = null, int $code = 404)
    {
        http_response_code($code);
        if (env('DEVELOPMENT')) {
            echo $message;
        }
        self::getInstance()->render();
    }

    static function getParameter($param, $strict = true, $numeric = true)
    {
        $router = self::getInstance();
        if (empty($router->dynamicSegments[$param])) {
            if ($strict) {
                self::abort(message: "Route parameter {$param} was not found");
            }
            return null;
        }
        if ($numeric and !is_numeric($router->dynamicSegments[$param])) {
            self::abort(message: "Route parameter {$param} is not numeric");
        }
        return $router->dynamicSegments[$param];
    }

    static function add($route, $path_to_include)
    {
        $router = self::getInstance();
        $_SESSION['current_route'] = $route;
        $callback = $path_to_include;
        if (!is_callable($callback)) {
            if (!strpos($path_to_include, '.php')) {
                $path_to_include .= '.php';
            }
        }
        if ($route == "/404") {
            $router->render($path_to_include);
        }

        $request_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
        $request_url = rtrim($request_url, '/');
        $request_url = strtok($request_url, '?');
        $route_parts = explode('/', $route);
        $request_url_parts = explode('/', $request_url);
        array_shift($route_parts);
        array_shift($request_url_parts);
        if ($route_parts[0] == '' && count($request_url_parts) == 0) {
            $router->render($path_to_include);
        }
        if (count($route_parts) != count($request_url_parts)) {
            return;
        }
        $parameters = [];
        foreach ($route_parts as $__i__ => $route_part) {
            if (preg_match("/^[$]/", $route_part)) {
                $route_part = ltrim($route_part, '$');
                array_push($parameters, $request_url_parts[$__i__]);
                $router->dynamicSegments[$route_part] = $request_url_parts[$__i__];
            } else if ($route_parts[$__i__] != $request_url_parts[$__i__]) {
                return;
            }
        }
        // Callback function
        if (is_callable($callback)) {
            call_user_func_array($callback, $parameters);
            exit;
        }

        $router->render($path_to_include);
    }
}