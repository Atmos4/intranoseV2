<?php

class Page
{
    private static self $instance;

    public string|false $title = "";
    public string $description = "";
    public string $css = "";
    public bool $nav = true;
    public string|false $heading = "";
    public string $content = "";
    public bool $controlled = false;

    /** singleton, so constructing and cloning should be prevented outside the object */
    protected function __construct()
    {
    }
    protected function __clone()
    {
    }

    public static function getInstance(): Page
    {
        self::$instance ??= new static();
        return self::$instance;
    }

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