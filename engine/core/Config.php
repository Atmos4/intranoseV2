<?php
class Config extends Singleton
{
    private $hashmap;

    protected function __construct()
    {
        $this->hashmap = include(base_path() . "/env.php");
    }

    static function get($key)
    {
        return self::getInstance()->hashmap[$key] ?? "";
    }
}