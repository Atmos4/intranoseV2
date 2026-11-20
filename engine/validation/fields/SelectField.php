<?php

class SelectField extends Field
{
    /** @var SelectFieldOption[] */
    public array $options = [];

    public bool $multiple = false;

    public function multiple(): static
    {
        $this->multiple = true;
        $this->value = is_array($this->value) ? array_values(array_filter(array_map('strval', $this->value), 'strlen')) : [];
        return $this;
    }

    public function render()
    {
        if ($this->multiple) {
            return $this->render_shoelace_multiple();
        }
        $result = "<select {$this->props()}>";
        foreach ($this->options as $option) {
            $selected = $this->value == $option->value ? " selected" : "";
            $result .= "<option value=\"$option->value\"$selected>$option->label</option>";
        }
        return $this->render_label($result . "</select>");
    }

    /** Multiple selection rendered with shoelace's <sl-select> (the native listbox is ugly ^^ ) */
    private function render_shoelace_multiple(): string
    {
        $id = Validator::keyToId($this->key);
        $values = htmlspecialchars(implode(" ", array_map('strval', (array) $this->value)));
        // htmx does not read shoelace values: see the htmx:configRequest listener in shoelace.php
        $attrs = "name=\"" . htmlspecialchars($this->key) . "[]\" id=\"$id\" multiple clearable"
            . ($values !== "" ? " value=\"$values\"" : "")
            . " placeholder=\"Sélectionner...\" max-options-visible=\"4\""
            . ($this->label ? " label=\"" . htmlspecialchars($this->label) . "\"" : "")
            . ($this->valid() ? "" : " aria-invalid=true")
            . ($this->disabled ? " disabled" : "")
            . ($this->required ? " required" : "")
            . $this->render_attrs();
        $result = "<sl-select $attrs>";
        foreach ($this->options as $option) {
            $result .= "<sl-option value=\"" . htmlspecialchars($option->value) . "\">"
                . htmlspecialchars($option->label) . "</sl-option>";
        }
        return $result . "</sl-select>";
    }

    public function option(string $value, string $label): static
    {
        $this->options[] = new SelectFieldOption($value, $label);
        return $this;
    }

    public function options(array $options): static
    {
        foreach ($options as $value => $label) {
            $this->option($value, $label);
        }
        return $this;
    }
}

class SelectFieldOption
{
    public function __construct(
        public string $value = "",
        public string $label = "",
        public bool $selected = false,
    ) {}
}
