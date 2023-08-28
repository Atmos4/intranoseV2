<?php
class MainMenu extends Singleton
{

    /** @var MainMenuItem[] */
    public array $items;

    function addItem(
        string $label,
        string $url,
        string $icon = "",
        bool $disableBoost = false
    ) {
        $this->items[] = new MainMenuItem($label, $url, $icon, $disableBoost);
        return $this;
    }
}

class MainMenuItem
{
    public string $label;
    public string $url;
    public string $icon;
    public bool $disableBoost;

    function __construct(
        string $label,
        string $url,
        string $icon = "",
        bool $disableBoost = false
    ) {
        $this->label = $label;
        $this->url = $url;
        $this->icon = $icon;
        $this->disableBoost = $disableBoost;
    }
}
