<?php

class Component
{
    public static array $_props = [];
    static function render(string $location, array $props = []): string
    {
        ob_start();
        self::$_props[ob_get_level()] = $props;
        include $location;
        self::$_props[ob_get_level()] = [];
        return ob_get_clean();
    }

    static function prop($key)
    {
        return self::$_props[ob_get_level()][$key] ?? null;
    }

    static function mounted()
    {
        return !empty(self::$_props[ob_get_level()]);
    }

    // TODO maybe move elsewhere some day;
    /** Helper function to transform a props array into a string of HTML attributes */
    static function attrs(array $props = []): string
    {
        return array_reduce(array_keys($props), fn($carry, $item) => ("$carry $item=\"" . htmlspecialchars($props[$item]) . "\""), "");
    }
}