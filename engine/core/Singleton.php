<?php
class Singleton
{
    private static $instances = [];

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct()
    {
    }
    protected function __clone()
    {
    }

    public static function getInstance(): static
    {
        $cls = static::class;
        self::$instances[$cls] ??= new static();
        return self::$instances[$cls];
    }
}