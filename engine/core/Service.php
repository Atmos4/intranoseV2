<?php
class Service
{
    /** @var callable[] */
    private static $factoryMethod = [];

    public static function create()
    {
        $cls = static::class;
        if (!isset(self::$factoryMethod[$cls])) {
            return new static();
        }
        return self::$factoryMethod[$cls]();
    }

    public static function setFactory(callable $factory)
    {
        self::$factoryMethod[static::class] ??= $factory;
    }
}