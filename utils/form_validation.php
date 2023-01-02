<?php

/** Sanitize the input of a form. Preferable to do on all fields because of form/request forging */
function clean($data)
{
    if ($data != null) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
    }
    return $data;
}

/** Factory method for the validator */
function validate($post)
{
    return new Validator($post);
}

/** Simple validation class */
class Validator
{
    /** @var Field[] */
    public array $fields = [];
    public bool $empty;

    public function __construct(array $post)
    {
        $this->empty = empty($post);
        foreach ($post as $key => $value) {
            $this->fields[$key] = new Field($key, $value);
        }
    }

    function value(string $key)
    {
        return clean($this->fields[$key]->value ?? null) ?? null;
    }

    function valid(string $key = null)
    {
        if ($key) {
            return $this->get_field($key)->valid();
        }
        return array_reduce($this->fields, function ($valid, Field $field) {
            return $valid && $field->valid();
        }, true);
    }

    function get_field(string $key): Field
    {
        return $this->fields[$key] ?? new Field($key);
    }

    function render_errors()
    {
        $errors = "";
        foreach ($this->fields as $field) {
            if ($field->error)
                $errors .= "<label for=\"$field->key\" class=\"error\">$field->label : $field->error</label>";
        }
        return $errors . "<br/><br/>";
    }

    function render_field(string $key)
    {
        return $this->get_field($key)->render();
    }

    /** Creates new number field */
    function number(string $key, string $msg = null): NumberField
    {
        $this->fields[$key] = new NumberField($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    /** Creates new text field */
    function string(string $key, string $msg = null): StringField
    {
        $this->fields[$key] = new StringField($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    /** Creates new date field */
    function date(string $key, string $msg = null): DateField
    {
        $this->fields[$key] = new DateField($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }
}

class Field
{
    public string $key;
    public string $label;
    public mixed $value;
    public ?string $error;
    /** Access to the validator for recursion and decorator pattern */
    public ?Validator $context;

    function __construct(string $key, mixed $value = null, $context = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->context = $context;
        $this->error = null;
        $this->label = "";
    }

    /**
     * Adds a label to the field
     * @param string $label
     * @return Field
     */
    function label(string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Renders the field as input
     * @param string $attrs
     * @return string
     */
    function render(string $attrs = "")
    {
        return "<label for='" . $this->key . "'>"
            . $this->label
            . "<input ${attrs} name=\"$this->key\" id=\"$this->key\" value=\"$this->value\" " . ($this->valid() ? "" : " aria-invalid=true") . "></label>";
    }

    /** Adds a validation error */
    protected function set_error($err)
    {
        $this->error = $err;
    }

    function valid()
    {
        return !$this->error;
    }

    /** Outputs a variable (Decorator pattern) */
    function out(&$var)
    {
        $var = $this->value;
        return $this;
    }

    /** Helper method to skip logic when $_POST is empty */
    protected function skip()
    {
        return $this->context->empty || $this->error;
    }

    /** Makes the field required */
    function required(string $msg = null)
    {
        if ($this->skip())
            return $this;
        if (!$this->value) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }


    /** Enables Decorator pattern */
    function number(string $key, string $msg = "")
    {
        return $this->context?->number($key, $msg) ?? new NumberField($key);
    }
    /** Enables Decorator pattern */
    function string(string $key, string $msg = "")
    {
        return $this->context?->string($key, $msg) ?? new StringField($key);
    }
    /** Enables Decorator pattern */
    function date(string $key, string $msg = "")
    {
        return $this->context?->date($key, $msg) ?? new DateField($key);
    }
}

class NumberField extends Field
{
    function check($msg = null)
    {
        if (!preg_match("/^[\d]*$/", $this->value)) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function render(string $attrs = "")
    {
        return parent::render("type = number");
    }

    /** Set upper limit for the number field */
    function max(int $count, string $msg = null)
    {
        if ($this->skip())
            return $this;
        if ($this->value > $count) {
            $this->set_error($msg ?? "Trop grand");
        }
        return $this;
    }

    /** Set lower limit for the number field */
    function min(int $count, string $msg = null)
    {
        if ($this->skip())
            return $this;
        if ($this->value < $count) {
            $this->set_error($msg ?? "Trop petit");
        }
        return $this;
    }
}

class DateField extends Field
{
    function check($msg = null)
    {
        if (!preg_match("/^[\d-]*$/", $this->value)) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function render(string $attrs = "")
    {
        return parent::render("type = date");
    }

    /** Set upper date limit */
    function before(string|null $date, string $msg = null)
    {
        if ($this->skip() || !$date)
            return $this;
        if (strtotime($this->value) > strtotime($date)) {
            $this->set_error($msg ?? "Trop tard");
        }
        return $this;
    }

    /** Set lower date limit */
    function after(string|null $date, string $msg = null)
    {
        if ($this->skip() || !$date)
            return $this;
        if (strtotime($this->value) < strtotime($date)) {
            $this->set_error($msg ?? "Trop tôt");
        }
        return $this;
    }
}

class StringField extends Field
{
    public ?string $placeholder = "";

    /** Defines the placeholder. Call the method without params to use the label as placeholder */
    function placeholder(string $text = null)
    {
        $this->placeholder = $text;
        return $this;
    }

    function check($msg = null)
    {
        if (!preg_match("/^[-\w\sÀ-ÿ]*$/", $this->value)) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function render(string $attrs = "")
    {
        $placeholder = $this->placeholder ?? $this->label;
        return parent::render("type = text placeholder=\"$placeholder\"");
    }

    function max_length(int $count, string $msg = null)
    {
        if ($this->skip())
            return $this;
        if (strlen($this->value) > $count) {
            $this->set_error($msg ?? "Trop long");
        }
        return $this;
    }

    function min_length(int $count, string $msg = null)
    {
        if ($this->skip())
            return $this;
        if (strlen($this->value) < $count) {
            $this->set_error($msg ?? "Trop court");
        }
        return $this;
    }
}