<?php
class SelectField extends Field
{
    /** @var SelectFieldOption[] */
    public array $options = [];

    function render()
    {
        $result = "<select {$this->props()}>";
        foreach ($this->options as $option) {
            $selected = $option->selected ? " selected" : "";
            $result .= "<option value=\"$option->value\"$selected>$option->label</option>";
        }
        return $this->render_label($result . "</select>");
    }

    function option(string $value, string $label): static
    {
        $this->options[] = new SelectFieldOption($value, $label, $this->value == $value);
        return $this;
    }

    function options(array $options): static
    {
        foreach ($options as $value => $label) {
            $this->option($value, $label);
        }
        return $this;
    }
}

class SelectFieldOption
{
    function __construct(
        public string $value = "",
        public string $label = "",
        public bool $selected = false,
    ) {
    }
}