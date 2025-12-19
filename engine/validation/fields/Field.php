<?php
class Field
{
    public string $key;
    public string $label;
    public FieldType $type;
    public mixed $value;
    public string $autocomplete = "off";
    public ?string $error;
    /** Access to the validator for recursion and decorator pattern */
    public ?Validator $context;
    public bool $disabled = false;
    public bool $readonly = false;
    public bool $required = false;
    private ?string $help;
    private array $attributes = [];

    function __construct(string $key, mixed $value = null, Validator $context = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->context = $context;
        $this->error = null;
        $this->label = "";
        $this->help = null;
        $this->set_type();
    }

    /**
     * Adds a label to the field
     * @param string $label
     */
    function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    function get_label(): string
    {
        return $this->label;
    }

    function attributes(array $attrs): static
    {
        $this->attributes = array_merge($this->attributes, $attrs);
        return $this;
    }

    function help(string $help): static
    {
        $this->help = $help;
        return $this;
    }

    function reset(): static
    {
        $this->value = null;
        return $this;
    }

    /**
     * Renders the field as input
     * @param string $attrs
     * @return string
     */
    function render()
    {

        return $this->render_label($this->render_core());
    }

    function __tostring()
    {
        return $this->render();
    }

    protected function render_core(): string
    {
        return "<input {$this->props()}>";
    }

    function props(bool $includeValue = true): string
    {
        $v = e($this->value);
        return
            "type=\"{$this->type->value}\" name=\"$this->key\" id=\"$this->key\""
            . ($includeValue ? " value=\"$v\"" : "")
            . ($this->valid() ? "" : " aria-invalid=true autofocus")
            . ($this->autocomplete ? " autocomplete = \"$this->autocomplete\"" : "")
            . ($this->disabled ? " disabled" : "")
            . ($this->required ? " required" : "")
            . ($this->readonly ? " readonly" : "")
            . $this->render_attrs();
    }

    protected function render_attrs(): string
    {
        return array_reduce(array_keys($this->attributes), fn($carry, $item) => ("$carry $item=\"" . htmlspecialchars($this->attributes[$item]) . "\""), "");
    }

    protected function render_label(string $input_render, bool $reverse = false): string
    {
        if ($this->label) {
            $label_content = $reverse ? $input_render . $this->label : $this->label . $input_render;
            $data_intro = $this->help ? ("data-intro=\"" . $this->help . "\"") : "";
            $input_render = "<label {$this->render_attrs()} $data_intro for=\"$this->key\">{$label_content}</label>";
        }
        return $input_render;
    }

    /** Adds a validation error */
    public function set_error(string $err): void
    {
        $this->error = $err;
    }

    function valid(): bool
    {
        return !$this->error;
    }

    /** Outputs a variable (Decorator pattern) */
    function out(string &$var): static
    {
        $var = $this->value;
        return $this;
    }

    protected function should_test(): bool
    {
        return !$this->context?->empty && !$this->error;
    }

    function disabled(): static
    {
        $this->disabled = true;
        return $this;
    }

    function readonly(): static
    {
        $this->readonly = true;
        return $this;
    }

    /** @param non-empty-string $preg */
    protected function test(string $preg, string $value = null): false|int
    {
        return preg_match($preg, $value ?? $this->value ?? "");
    }

    /** Makes the field required */
    function required(string $msg = null): static
    {
        $this->required = true;
        if ($this->should_test() && !$this->value) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }

    function check(string $msg = null): void
    {
    }

    protected function set_type(): void
    {
        $this->type = FieldType::Text;
    }

    /** Add a condition that if false will set the according error message */
    function condition(bool $condition, string $error_msg): static
    {
        if ($this->should_test() and !$condition) {
            $this->set_error($error_msg);
        }
        return $this;
    }

    /** See more information: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/autocomplete */
    function autocomplete(string $autocomplete = ""): static
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }
}

enum FieldType: string
{
    case Text = "text";
    case Date = "date";
    case DateTime = "datetime-local";
    case Number = "number";
    case Email = "email";
    case Phone = "phone";
    case Password = "password";
    case Checkbox = "checkbox";
    case File = "file";
    case Url = "url";
}