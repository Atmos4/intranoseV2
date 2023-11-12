<?php
// Note: this is heavily inspired from the singleton pattern, but without most of the drawbacks.

/** Base for all services. The instance needs to be boostrapped first, then can be used globally. */
class ServiceBase
{
    private static $instances = [];

    public static function getInstance(): static
    {
        $cls = static::class;
        return self::$instances[$cls] ?? throw new Exception("Service $cls not initialized");
    }

    /** @param static $instance */
    public static function init($instance)
    {
        self::$instances[static::class] ??= $instance;
    }

    /** Reset instances for unit tests */
    public static function reset($className = null)
    {
        if ($className) {
            self::$instances[$className] = null;
        } else {
            self::$instances = [];
        }
    }
}