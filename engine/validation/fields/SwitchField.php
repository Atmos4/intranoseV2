<?php
class SwitchField extends Field
{
    function set_type(): void
    {
        $this->type = FieldType::Checkbox;
    }

    public string $true_label = "";
    public string $false_label = "";

    function check(string $msg = null): void
    {
        if (!$this->test('/\D*$/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function set_labels(string $true_label, string $false_label): static
    {
        $this->true_label = $true_label;
        $this->false_label = $false_label;
        return $this;
    }

    function get_label(): string
    {
        return $this->true_label;
    }

    function render()
    {
        return "<label for=\"$this->key\">"
            . "<input role=switch value=1 " . $this->props() . ($this->value ? " checked" : "") . ">"
            . ($this->true_label && $this->false_label ?
                "<ins>$this->true_label <i class=\"fas fa-check\"></i></ins><del>$this->false_label <i class=\"fas fa-xmark\"></i></del>"
                : $this->label)
            . "</label>";
    }
}