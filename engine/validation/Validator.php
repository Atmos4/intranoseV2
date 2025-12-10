<?php
/** Simple validation class */
class Validator
{
    /** @var Field[] */
    public array $fields = [];
    public bool $empty = true;
    public string|null $action;
    public string|null $success = null;
    public string $error_msg = "";


    function __construct(array $form_values = [], $action = null)
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

    function value(string $key)
    {
        return $this->fields[$key]->value ?? null;
    }

    function valid(string $key = null)
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

    function get_field(string $key): Field
    {
        return $this->fields[$key] ?? new Field($key);
    }

    /**
     * To be used with a standalone hx-post / hx-delete
     * @return string
     */
    function hx_action($vals = []): string
    {
        $vals["action"] = $this->action;
        $vals["csrf"] = gen_csrf();
        return "hx-vals='" . json_encode($vals) . "'";
    }

    function render_validation(): string
    {
        $result = "";

        // Add form action name
        if ($this->action)
            $result .= "<input type=\"hidden\" name=\"action\" value=\"$this->action\">";

        // Add csrf
        $result .= set_csrf();

        foreach ($this->fields as $field) {
            if ($field->error) {
                $label = $field->get_label();
                $result .= "<label for=\"$field->key\" class=\"error\">" .
                    ($label ? "{$field->get_label()} : " : "") . "$field->error</label>";
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

    function __tostring()
    {
        return $this->render_validation();
    }

    function set_success($success)
    {
        $this->success = $success;
    }

    function set_error($message)
    {
        $this->error_msg = $message;
    }

    function render_field(string $key)
    {
        return $this->get_field($key)->render();
    }

    /** 
     * Magic function to create a certain field type
     */
    private function create($key, $field, $msg)
    {
        $this->fields[$key] = new $field($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    /** Creates new number field */
    function number(string $key, string $msg = null): NumberField
    {
        return $this->create($key, NumberField::class, $msg);
    }

    /** Creates new text field */
    function text(string $key, string $msg = null): StringField
    {
        return $this->create($key, StringField::class, $msg);
    }

    function textarea(string $key, string $msg = null): TextAreaField
    {
        return $this->create($key, TextAreaField::class, $msg);
    }

    /** Creates new date field */
    function date(string $key, string $msg = null): DateField
    {
        return $this->create($key, DateField::class, $msg);
    }

    function date_time(string $key, string $msg = null): DateTimeField
    {
        return $this->create($key, DateTimeField::class, $msg);
    }

    function switch(string $key, string $msg = null): SwitchField
    {
        return $this->create($key, SwitchField::class, $msg);
    }

    function upload(string $key, string $msg = null): UploadField
    {
        return $this->create($key, UploadField::class, $msg);
    }

    function email(string $key, string $msg = null): EmailField
    {
        return $this->create($key, EmailField::class, $msg);
    }

    function phone(string $key, string $msg = null): PhoneField
    {
        return $this->create($key, PhoneField::class, $msg);
    }

    function password(string $key, string $msg = null): PasswordField
    {
        return $this->create($key, PasswordField::class, $msg);
    }

    function select(string $key): SelectField
    {
        return $this->create($key, SelectField::class, null);
    }

    function url(string $key): UrlField
    {
        return $this->create($key, UrlField::class, null);
    }
}