<?php
class SingletonDependency extends FactoryDependency
{
    private static $instances = [];

    public static function getInstance(): static
    {
        $cls = static::class;
        self::$instances[$cls] ??= self::create();
        return self::$instances[$cls];
    }

    public static function reset()
    {
        unset(self::$instances[static::class]);
    }
}