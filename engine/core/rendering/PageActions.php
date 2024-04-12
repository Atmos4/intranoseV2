<?php

class PageActionBuilder extends OutputBuilder
{
    function __toString()
    {
        return '<nav id="page-actions">'
            . implode($this->output)
            . '</nav>';
    }

    function back($href, $label = "Retour"): self
    {
        $this->link($href, $label, "fa-caret-left", ["class" => "secondary"]);
        return $this;
    }

    /**
     * @param Closure(PageActionDropdownBuilder):void|PageActionDropdownBuilder $functor 
     * @return static 
     */
    function dropdown(callable $functor): static
    {
        $functor($this->output[] = new PageActionDropdownBuilder($this));
        return $this;
    }
}

class PageActionDropdownBuilder extends OutputBuilder
{
    function __construct(public PageActionBuilder $parent)
    {
    }

    function __toString()
    {
        $output = array_reduce($this->output, fn($c, $i) => ("$c<li>$i</li>"), "");
        return <<<HTML
        <li>
            <details class="dropdown">
                <summary>Actions</summary>
                <ul dir="rtl">$output</ul>
            </details>
        </li>
        HTML;
    }
}

class OutputBuilder
{
    public array $output = [];

    function link($href, $label, $icon = "", $attributes = []): static
    {
        $this->output[] = "<a href=\"$href\" {$this->attrs($attributes)}>{$this->iconLabel($label, $icon)}</a>";
        return $this;
    }

    function submit($label): static
    {
        $this->output[] = "<button type=\"submit\">$label</button>";
        return $this;
    }

    protected function attrs(array $attrs = []): string
    {
        return array_reduce(array_keys($attrs), fn($c, $i) => ("$c $i=\"" . htmlspecialchars($attrs[$i]) . "\""), "");
    }

    protected function iconLabel($label, $icon): string
    {
        return $icon ? "<i class=\"fa fa-fw $icon\"></i> $label" : $label;
    }
}