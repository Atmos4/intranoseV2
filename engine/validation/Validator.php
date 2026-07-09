<?php

/** Simple validation class */
class Validator
{
    /** @var Field[] */
    public array $fields = [];
    public bool $empty = true;
    public ?string $action;
    public ?string $success = null;
    public string $error_msg = "";


    public function __construct(array $form_values = [], $action = null)
    {
        $this->action = $action;
        if ((!empty($_POST) or !empty($_FILES)) and ((!$action && !isset($_POST['action'])) or $_POST['action'] == $action)) {
            $this->empty = false;
            $form_values = $_POST;
        }

        foreach ($form_values as $key => $value) {
            $this->fields[$key] = new Field($key, $value);
        }
    }

    public function value(string $key)
    {
        return $this->fields[$key]->value ?? null;
    }

    public function valid(string $key = null)
    {
        if ($key) {
            return $this->get_field($key)->valid();
        }

        if ($this->empty || !is_csrf_valid() || $this->error_msg) {
            return false;
        }
        return array_reduce($this->fields, function ($valid, Field $field) {
            return $valid && $field->valid();
        }, true);
    }

    public function get_field(string $key): Field
    {
        return $this->fields[$key] ?? new Field($key);
    }

    /**
     * To be used with a standalone hx-post / hx-delete
     * @return string
     */
    public function hx_action($vals = []): string
    {
        $vals["action"] = $this->action;
        $vals["csrf"] = gen_csrf();
        return "hx-vals='" . json_encode($vals) . "'";
    }

    public function render_validation(): string
    {
        $result = "";

        // Add form action name
        if ($this->action) {
            $result .= "<input type=\"hidden\" name=\"action\" value=\"$this->action\">";
        }

        // Add csrf
        $result .= set_csrf();

        foreach ($this->fields as $field) {
            if ($field->error) {
                $label = $field->get_label();
                $id = self::keyToId($field->key);
                $result .= "<label for=\"{$id}\" class=\"error\">"
                    . ($label ? "{$field->get_label()} : " : "") . "$field->error</label>";
            }
        }
        if (!$this->empty) {
            if ($this->error_msg) {
                $result .= "<label class=\"error\">$this->error_msg</label>";
            } elseif ($this->valid() && $this->success) {
                $result .= "<ins>$this->success</ins><br>";
            }
        }
        return $result;
    }

    public function __tostring()
    {
        return $this->render_validation();
    }

    public function set_success($success)
    {
        $this->success = $success;
    }

    public function set_error($message)
    {
        $this->error_msg = $message;
    }

    public function render_field(string $key)
    {
        return $this->get_field($key)->render();
    }

    /**
     * Converts a bracket-notation key like "user[0][first_name]" to a flat
     * HTML-safe id like "user_0_first_name" suitable for id/for attributes.
     */
    public static function keyToId(string $key): string
    {
        return rtrim(preg_replace('/\[([^\]]*)\]/', '_$1', $key), '_');
    }

    /**
     * Resolves a bracket-notation key against a nested array.
     * e.g. "user[0][first_name]" against $_POST returns $_POST['user'][0]['first_name']
     */
    public static function resolveNestedValue(array $data, string $key): mixed
    {
        preg_match_all('/([^\[\]]+)/', $key, $matches);
        $current = $data;
        foreach ($matches[1] as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }
        return $current;
    }

    /**
     * Magic function to create a certain field type
     */
    private function create($key, $field, $msg)
    {
        $value = $this->empty ? ($this->fields[$key]->value ?? null) : self::resolveNestedValue($_POST, $key);
        $this->fields[$key] = new $field($key, $value, $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    /** Creates new number field */
    public function number(string $key, string $msg = null): NumberField
    {
        return $this->create($key, NumberField::class, $msg);
    }

    /** Creates new text field */
    public function text(string $key, string $msg = null): StringField
    {
        return $this->create($key, StringField::class, $msg);
    }

    public function textarea(string $key, string $msg = null): TextAreaField
    {
        return $this->create($key, TextAreaField::class, $msg);
    }

    /** Creates new date field */
    public function date(string $key, string $msg = null): DateField
    {
        return $this->create($key, DateField::class, $msg);
    }

    public function date_time(string $key, string $msg = null): DateTimeField
    {
        return $this->create($key, DateTimeField::class, $msg);
    }

    public function switch(string $key, string $msg = null): SwitchField
    {
        return $this->create($key, SwitchField::class, $msg);
    }

    public function upload(string $key, string $msg = null): UploadField
    {
        return $this->create($key, UploadField::class, $msg);
    }

    public function email(string $key, string $msg = null): EmailField
    {
        return $this->create($key, EmailField::class, $msg);
    }

    public function phone(string $key, string $msg = null): PhoneField
    {
        return $this->create($key, PhoneField::class, $msg);
    }

    public function password(string $key, string $msg = null): PasswordField
    {
        return $this->create($key, PasswordField::class, $msg);
    }

    public function select(string $key): SelectField
    {
        return $this->create($key, SelectField::class, null);
    }

    public function url(string $key): UrlField
    {
        return $this->create($key, UrlField::class, null);
    }
}
