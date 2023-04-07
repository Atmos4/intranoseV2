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
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }
}

class Page extends Singleton
{
    public string|false $title = "";
    public string $description = "";
    public string $css = "";
    public bool $nav = true;
    public string|false $heading = "";
    public string $content = "";
    public bool $controlled = false;

    public function css(string $css)
    {
        $this->css = "/assets/css/" . $css;
        return $this;
    }
    public function description(string $description)
    {
        $this->description = $description;
        return $this;
    }
    public function heading(string|false $heading)
    {
        $this->heading = $heading;
        return $this;
    }
    public function disableNav()
    {
        $this->nav = false;
        return $this;
    }
    public function title(string $title)
    {
        $this->title = $title;
        return $this;
    }
    public function controlled()
    {
        $this->controlled = true;
        return $this;
    }
    public function setContent($content)
    {
        $this->content = $content;
    }
}

class Env extends Singleton
{
    private $hashmap;

    protected function __construct()
    {
        $this->hashmap = include("env.php");
    }

    function getValue($key)
    {
        return $this->hashmap[$key] ?? "";
    }
}