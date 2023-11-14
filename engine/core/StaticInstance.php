<?php
/** Modified singleton. Needs to be initialized during config and then acts as a global object */
class StaticInstance
{
    private static $instances = [];

    public static function getInstance(): static
    {
        $cls = static::class;
        return self::$instances[$cls] ?? throw new Exception("Instance $cls not initialized");
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