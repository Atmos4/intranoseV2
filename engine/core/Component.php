<?php

class Component
{
    public static array $_props = [];
    static function render(string $location, array $props = []): string
    {
        self::$_props = $props;
        ob_start();
        include $location;
        $_props = [];
        return ob_get_clean();
    }

    static function prop($key)
    {
        return self::$_props[$key] ?? null;
    }

    static function mounted()
    {
        return !empty(self::$_props);
    }
}