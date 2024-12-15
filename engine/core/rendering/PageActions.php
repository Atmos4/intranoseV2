<?php

class PageActionBuilder extends OutputBuilder
{
    function __construct($condition = true)
    {
        $this->condition = $condition;
    }

    function __toString()
    {
        return $this->condition ? '<nav id="page-actions">'
            . implode($this->output)
            . '</nav>' : "";
    }

    /** Add a special link to return to the previous scene */
    function back($href, $label = "Retour", $icon = "fa-caret-left"): self
    {
        return $this->link($href, $label, $icon, ["class" => "secondary"]);
    }

    /**
     * @param Closure(PageActionDropdownBuilder):void|PageActionDropdownBuilder $functor 
     * @return static 
     */
    function dropdown(callable $functor, $label = "Actions", $attributes = []): static
    {
        $this->condition && $functor($this->output[] = new PageActionDropdownBuilder($label, $this, !!$this->output, $attributes));
        return $this;
    }
}

class PageActionDropdownBuilder extends OutputBuilder
{
    function __construct(public string $label, public PageActionBuilder|null $parent = null, public bool $rtl = false, public array $attributes = [], public bool $standalone = false)
    {
    }

    function __toString()
    {
        if (!$this->condition) {
            return "";
        }
        $output = array_reduce($this->output, fn($c, $i) => ("$c<li>$i</li>"), "");
        $rtl = $this->rtl ? 'dir="rtl"' : '';
        $dropdown = <<<HTML
            <details class="dropdown" {$this->attrs($this->attributes)}>
                <summary>$this->label</summary>
                <ul $rtl>$output</ul>
            </details>
        HTML;
        if ($this->standalone) {
            return $dropdown;
        }
        return <<<HTML
            <li>
                $dropdown
            </li>
        HTML;
    }

    /* Override iconLabel to insert fa-fw for consistent spacing */
    protected function iconLabel($label, $icon): string
    {
        return parent::iconLabel($label, $icon ? "$icon fa-fw" : $icon);
    }
}

class OutputBuilder
{
    public array $output = [];
    public $condition = true;

    /**
     * @param Closure(static):void|static $functor 
     * @param (Closure(static):void|static)|null $elseFunctor 
     * @return static 
     */
    function if($condition, $functor, $elseFunctor = null)
    {
        if ($condition) {
            $functor($this);
        } elseif ($elseFunctor) {
            $elseFunctor();
        }
        return $this;
    }

    protected function add($s)
    {
        if ($this->condition) {
            $this->output[] = $s;
        }
        return $this;
    }

    /** Add a link */
    function link($href, $label, $icon = "", $attributes = []): static
    {
        return $this->add("<a href=\"$href\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</a>");
    }

    /** Add a submit button */
    function submit($label, $icon = "", $attributes = []): static
    {
        return $this->add("<button type=\"submit\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</button>");
    }

    /** Add a normal button */
    function button($label, $icon = "", $attributes = []): static
    {
        return $this->add("<button type=\"button\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</button>");
    }

    /** Generate attributes from an array of [name]:value */
    protected function attrs(array $attrs = []): string
    {
        return array_reduce(array_keys($attrs), fn($c, $i) => ("$c $i=\"" . htmlspecialchars($attrs[$i]) . "\""), "");
    }

    /** Generates a proper label with an icon */
    protected function iconLabel($label, $icon): string
    {
        return $icon ? "<i class=\"fa $icon\"></i> $label" : $label;
    }
}