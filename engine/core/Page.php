<?php
class Page extends SingletonDependency
{
    public string|false $title = "";
    public string $description = "";
    public array $css_files = [];
    public bool $nav = true;
    public string|false $heading = "";
    public string $content = "";
    public bool $controlled = false;
    public array $scripts = [];

    public function css(string $css)
    {
        $this->css_files[] = "/assets/css/$css";
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
    public function script($script)
    {
        $this->scripts[] = "/assets/js/$script.js";
        return $this;
    }
}