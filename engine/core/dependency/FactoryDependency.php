<?php
class FactoryDependency
{
    /** @var callable[] */
    private static $factoryMethods = [];

    public static function create(): static
    {
        $cls = static::class;
        if (!isset(self::$factoryMethods[$cls])) {
            return new static();
        }
        return self::$factoryMethods[$cls]();
    }

    public static function factory(callable $factory)
    {
        self::$factoryMethods[static::class] ??= $factory;
    }
}