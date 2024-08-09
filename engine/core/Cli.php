<?php

class Cli
{
    public static function color($text, $color)
    {
        return "\e[{$color}m$text\e[0m";
    }

    private static function check()
    {
        return self::color("\u{2713}", 32);
    }
    private static function cross()
    {
        return self::color("\u{2716}", 31);
    }
    private static function warn()
    {
        return self::color("!", 31);
    }

    static function ok($m)
    {
        return self::check() . " $m" . PHP_EOL;
    }
    static function error($m)
    {
        return self::cross() . " $m" . PHP_EOL;
    }
    static function abort($m)
    {
        echo self::error($m);
        exit(1);
    }
    static function warning($m)
    {
        return self::warn() . " $m" . PHP_EOL;
    }
    static function success($m = "All done")
    {
        exit(PHP_EOL . "$m \u{2728}" . PHP_EOL);
    }
}