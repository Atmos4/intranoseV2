<?php

/** Sanitize the input of a form. Preferable to do on all fields because of form/request forging */
function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate($post)
{
    if (empty($post)) {
        return null;
    }
    return new Validator($post);
}

/** Simple validation class */
class Validator
{
    public array $fields = [];

    public function __construct(array $post)
    {
        foreach ($post as $key => $value) {
            $this->fields[$key] = new Field($value);
        }
    }

    function value(string $key)
    {
        return clean($this->fields[$key]->value ?? null) ?? null;
    }

    function valid()
    {
        return array_reduce($this->fields, function ($valid, Field $field) {
            return $valid && $field->valid();
        }, true);
    }

    function error(string $key)
    {
        return $this->fields[$key]?->error ?? "";
    }

    function field(string $key): Field
    {
        return $this->fields[$key] ?? new Field();
    }

    function render_error(string $key)
    {
        return "<span class='error'>" . $this->error($key) . "</span>";
    }

    function render_input(string $key)
    {
        return "value=\"" . $this->value($key) . "\"" . (!$this->error($key) ? "" : " aria-invalid=true");
    }


    function number(string $key, string $msg = null): NumberField
    {
        $this->fields[$key] = new NumberField($this->value($key), $this->error($key));
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    function string(string $key, string $msg = null): StringField
    {
        $this->fields[$key] = new StringField($this->value($key),  $this->error($key));
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    function date(string $key, string $msg = null): DateField
    {
        $this->fields[$key] = new DateField($this->value($key), $this->error($key));
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }
}

class Field
{
    public mixed $value;
    //protected ?Validator $context;
    public ?string $error;

    function __construct(mixed $value = null, $error = null)
    {
        $this->value = $value;
        //$this->context = $context;
        $this->error = $error;
    }

    function set_error($err)
    {
        if (empty($this->error)) $this->error =  $err;
    }

    function valid()
    {
        return empty($this->error);
    }

    protected function skip()
    {
        return $this->value == null || !$this->valid();
    }

    function required(string $msg = null)
    {
        if (!$this->value) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }


    // function number(string $key, string $msg = "")
    // {
    //     return $this->context?->number($key, $msg) ?? new NumberField();
    // }
    // function string(string $key, string $msg = "")
    // {
    //     return $this->context?->string($key, $msg) ?? new StringField();
    // }
}

class NumberField extends Field
{
    function check($msg = null)
    {
        if (!preg_match("/^[\d]*$/", $this->value)) {
            $this->set_error($msg ?? "Format invalide");
        }
    }
    function max(int $count, string $msg = null)
    {
        if ($this->skip()) return $this;
        if ($this->value > $count) {
            $this->set_error($msg ?? "Trop grand");
        }
        return $this;
    }

    function min(int $count, string $msg = null)
    {
        if ($this->skip()) return $this;
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
    function before(string $date, string $msg = null)
    {
        if ($this->skip()) return $this;
        if (strtotime($this->value) > strtotime($date)) {
            $this->set_error($msg ?? "Trop tard");
        }
        return $this;
    }

    function after(string $date, string $msg = null)
    {
        if ($this->skip()) return $this;
        if (strtotime($this->value) < strtotime($date)) {
            $this->set_error($msg ?? "Trop tôt");
        }
        return $this;
    }
}

class StringField extends Field
{
    function check($msg = null)
    {
        if (!preg_match("/^[\w\s]*$/", $this->value)) {
            $this->set_error($msg ?? "Format invalide");
        }
    }
    function max_length(int $count, string $msg = null)
    {
        if ($this->skip()) return $this;
        if (strlen($this->value) > $count) {
            $this->set_error($msg ?? "Trop long");
        }
        return $this;
    }

    function min_length(int $count, string $msg = null)
    {
        if ($this->skip()) return $this;
        if (strlen($this->value) < $count) {
            $this->set_error($msg ?? "Trop court");
        }
        return $this;
    }
}
