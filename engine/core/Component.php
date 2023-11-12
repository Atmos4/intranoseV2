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

    // TODO maybe move elsewhere some day;
    /** Helper function to transform a props array into a string of HTML attributes */
    static function attrs(array $props = []): string
    {
        return array_reduce(array_keys($props), fn($carry, $item) => ("$carry $item=\"" . htmlspecialchars($props[$item]) . "\""), "");
    }
}