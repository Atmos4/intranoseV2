<?php
class Router extends Singleton
{

    private array $dynamicSegments;
    private int $level = 0;

    private function render($path_to_include = "pages/404.php")
    {
        try {
            $this->level = ob_get_level();
            ob_start();
            include_once app_path() . "/$path_to_include";
            $page = Page::getInstance();
            if ($page->title) {
                $content = ob_get_clean();
                Page::getInstance()->setContent($content);
                ob_start();
                require_once app_path() . "/template/layout.php";
            } else {
                Toast::renderOob();
            }
            ob_end_flush();
        } catch (Throwable $e) {
            $this->cleanBuffer();
            logger()->error($e->getMessage());
            Toast::error("Une erreur est survenue");
            Toast::renderOob();
        }
        exit();
    }

    private function cleanBuffer(): self
    {
        while (ob_get_level() > $this->level) {
            ob_end_clean();
        }
        return $this;
    }

    static function abort(string $message = null, int $code = 404)
    {
        http_response_code($code);
        Page::reset();
        $instance = self::getInstance()->cleanBuffer();
        if (env('DEVELOPMENT')) {
            echo $message;
        }
        $instance->render();
    }

    static function getParameter($param, $strict = true, $numeric = true, $pattern = null)
    {
        $router = self::getInstance();
        if (empty($router->dynamicSegments[$param])) {
            if ($strict) {
                self::abort(message: "Route parameter $param was not found");
            }
            return null;
        }
        $found_param = $router->dynamicSegments[$param];
        if (!$pattern and $numeric and !is_numeric($found_param)) {
            self::abort(message: "Route parameter $param is not numeric");
        }
        if ($pattern and !preg_match($pattern, $found_param)) {
            self::abort(message: "Route parameter $param doesn't match pattern $pattern");
        }
        return $found_param;
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
        $_SESSION['request_url'] = $request_url;
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