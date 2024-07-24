<?php

class PageActionBuilder extends OutputBuilder
{
    function __toString()
    {
        return '<nav id="page-actions">'
            . implode($this->output)
            . '</nav>';
    }

    /** Add a special link to return to the previous scene */
    function back($href, $label = "Retour", $icon = "fa-caret-left"): self
    {
        $this->link($href, $label, $icon, ["class" => "secondary"]);
        return $this;
    }

    /**
     * @param Closure(PageActionDropdownBuilder):void|PageActionDropdownBuilder $functor 
     * @return static 
     */
    function dropdown(callable $functor, $label = "Actions"): static
    {
        $functor($this->output[] = new PageActionDropdownBuilder($label, $this, !!$this->output));
        return $this;
    }
}

class PageActionDropdownBuilder extends OutputBuilder
{
    function __construct(public string $label, public PageActionBuilder|null $parent = null, public bool $rtl = false)
    {
    }

    function __toString()
    {
        $output = array_reduce($this->output, fn($c, $i) => ("$c<li>$i</li>"), "");
        $rtl = $this->rtl ? 'dir="rtl"' : '';
        return <<<HTML
        <li>
            <details class="dropdown">
                <summary>$this->label</summary>
                <ul $rtl>$output</ul>
            </details>
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

    /**
     * @param Closure(static):void|static $functor 
     * @param (Closure(static):void|static)|null $elseFunctor 
     * @return static 
     */
    function if($condition, $functor, $elseFunctor = null)
    {
        if ($condition) {
            $functor($this);
        } else {
            $elseFunctor && $elseFunctor();
        }
        return $this;
    }

    /** Add a link */
    function link($href, $label, $icon = "", $attributes = []): static
    {
        $this->output[] = "<a href=\"$href\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</a>";
        return $this;
    }

    /** Add a submit button */
    function submit($label, $icon = "", $attributes = []): static
    {
        $this->output[] = "<button type=\"submit\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</button>";
        return $this;
    }

    /** Add a normal button */
    function button($label, $icon = "", $attributes = []): static
    {
        $this->output[] = "<button type=\"button\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</button>";
        return $this;
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