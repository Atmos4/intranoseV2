<?php

class Component
{
    public int $level = 0;

    public array $sections = [];
    public string|null $currentSection = null;

    public static array $_props = [];

    function __construct(public string $location, public array $props = [])
    {
    }

    function __toString()
    {
        return $this->render();
    }

    function render(array $props = []): string
    {
        ob_start();
        $level = ob_get_level();
        self::$_props[$level] = $props;
        include $this->location;
        self::$_props[$level] = [];
        return ob_get_clean();
    }

    static function prop($key)
    {
        return self::$_props[ob_get_level()][$key] ?? null;
    }

    function open(array $props = []): self
    {
        ob_start();
        if ($props)
            $this->props = $props;
        $this->level = ob_get_level();
        return $this;
    }

    function close()
    {
        $l = ob_get_level();
        if ($l != $this->level)
            throw new Exception("output buffer level doesn't match");
        return $this->render([...$this->props, "children" => ob_get_clean(), "sections" => $this->sections]);
    }

    function start($sectionName)
    {
        $this->currentSection = $sectionName;
        ob_start();
    }

    function stop(): self
    {
        if ($this->currentSection)
            $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
        return $this;
    }

    static function children()
    {
        return self::prop("children");
    }

    static function section($name)
    {
        $s = self::prop("sections");
        return $s && is_array($s) ? $s[$name] ?? null : null;
    }

    static function mounted()
    {
        return !empty(self::$_props[ob_get_level()]);
    }

    // TODO maybe move elsewhere some day;
    /** Helper function to transform a props array into a string of HTML attributes */
    static function attrs(array $props = []): string
    {
        return array_reduce(array_keys($props), fn($carry, $item) => ("$carry $item=\"" . htmlspecialchars($props[$item]) . "\""), "");
    }
}