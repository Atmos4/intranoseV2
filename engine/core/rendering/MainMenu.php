<?php

class MainMenu extends FactoryDependency
{
    /** @var MainMenuItem[] */
    public array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function addItem(
        string $label,
        string $url,
        string $icon = "",
        bool $disableBoost = false,
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

    public function __construct(
        string $label,
        string $url,
        string $icon = "",
        bool $disableBoost = false,
    ) {
        $this->label = $label;
        $this->url = $url;
        $this->icon = $icon;
        $this->disableBoost = $disableBoost;
    }
}
