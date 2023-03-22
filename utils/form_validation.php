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
function validate($post = [], string|null $action = null)
{
    return new Validator($post, $action);
}

/** Simple validation class */
class Validator
{
    /** @var Field[] */
    public array $fields = [];
    public bool $empty = true;
    public string|null $action;
    public string|null $success = null;


    public function __construct(array $form_values, $action = null)
    {
        $this->action = $action;
        if ((!empty($_POST) or !empty($_FILES)) and (!$action or $_POST['action'] == $action)) {
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

        if ($this->empty || !is_csrf_valid()) {
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

    function render_validation()
    {
        $result = "";

        // Add form action name
        if ($this->action)
            $result .= "<input type=\"hidden\" name=\"action\" value=\"$this->action\">";

        // Add csrf
        $result .= set_csrf();

        foreach ($this->fields as $field) {
            if ($field->error) {
                $result .= "<label for=\"$field->key\" class=\"error\">{$field->get_label()} : $field->error</label>";
            }
        }
        if (!$this->empty) {
            if ($this->valid()) {
                $result .= "<ins>$this->success</ins>";
            }
            $result .= "<br/><br/>";
        }
        return $result;
    }

    function set_success($success)
    {
        $this->success = $success;
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
        return $this->create($key, NumberField::class, $msg)->type(FieldType::Number);
    }

    /** Creates new text field */
    function text(string $key, string $msg = null): StringField
    {
        return $this->create($key, StringField::class, $msg)->type(FieldType::Text);
    }

    /** Creates new date field */
    function date(string $key, string $msg = null): DateField
    {
        return $this->create($key, DateField::class, $msg)->type(FieldType::Date);
    }

    /** Creates new date field */
    function switch (string $key, string $msg = null): SwitchField
    {
        return $this->create($key, SwitchField::class, $msg)->type(FieldType::Checkbox);
    }

    function upload(string $key, string $msg = null): UploadField
    {
        return $this->create($key, UploadField::class, $msg)->type(FieldType::File);
    }

    function email(string $key, string $msg = null): EmailField
    {
        return $this->create($key, EmailField::class, $msg)->type(FieldType::Email);
    }

    function phone(string $key, string $msg = null): PhoneField
    {
        return $this->create($key, PhoneField::class, $msg)->type(FieldType::Phone);
    }

    function password(string $key, string $msg = null): PasswordField
    {
        return $this->create($key, PasswordField::class, $msg)->type(FieldType::Password);
    }


}

enum FieldType: string
{
    case Text = "text";
    case Date = "date";
    case Number = "number";
    case Email = "email";
    case Phone = "phone";
    case Password = "password";
    case Checkbox = "checkbox";
    case File = "file";
}

class Field
{
    public string $key;
    public string $label;
    public FieldType $type = FieldType::Text;
    public mixed $value;
    public string $autocomplete = "off";
    public ?string $error;
    /** Access to the validator for recursion and decorator pattern */
    public ?Validator $context;

    public bool $disabled = false;

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

    function get_label()
    {
        return $this->label;
    }

    /**
     * Renders the field as input
     * @param string $attrs
     * @return string
     */
    function render(string $attrs = "")
    {
        return "<label for=\"$this->key\">$this->label<input type=\"{$this->type->value}\" $attrs name=\"$this->key\" id=\"$this->key\" value=\"$this->value\" "
            . ($this->valid() ? "" : " aria-invalid=true")
            . ($this->autocomplete ? " autocomplete = \"$this->autocomplete\"" : "")
            . ($this->disabled ? " disabled" : "")
            . "></label>";
    }

    /** Adds a validation error */
    public function set_error($err)
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

    protected function should_test(): bool
    {
        return !$this->context->empty && !$this->error;
    }

    function disabled()
    {
        $this->disabled = true;
        return $this;
    }

    protected function test(string $preg)
    {
        return preg_match($preg, $this->value ?? "");
    }

    /** Makes the field required */
    function required(string $msg = null)
    {
        if ($this->should_test() && !$this->value) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }

    function check(string $msg = null)
    {
    }

    /** Add a condition that if false will set the according error message */
    function condition(bool $condition, string $error_msg)
    {
        if ($this->should_test() and !$condition) {
            $this->set_error($error_msg);
        }
    }

    function type(FieldType $type): object
    {
        $this->type = $type;
        return $this;
    }

    /** See more information: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/autocomplete */
    function autocomplete($autocomplete = "")
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }
}

class NumberField extends Field
{
    function check(string $msg = null)
    {
        if (!$this->test("/^[\d]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper limit for the number field */
    function max(int $count, string $msg = null)
    {
        if ($this->should_test() && $this->value > $count) {
            $this->set_error($msg ?? "Trop grand");
        }
        return $this;
    }

    /** Set lower limit for the number field */
    function min(int $count, string $msg = null)
    {
        if ($this->should_test() && $this->value < $count) {
            $this->set_error($msg ?? "Trop petit");
        }
        return $this;
    }
}

class DateField extends Field
{
    function check(string $msg = null)
    {
        if (!$this->test("/^[\d-]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper date limit */
    function before(string|null $date, string $msg = null)
    {
        if ($this->should_test() && $date && strtotime($this->value) > strtotime($date)) {
            $this->set_error($msg ?? "Trop tard");
        }
        return $this;
    }

    /** Set lower date limit */
    function after(string|null $date, string $msg = null)
    {
        if ($this->should_test() && $date && strtotime($this->value) < strtotime($date)) {
            $this->set_error($msg ?? "Trop tôt");
        }
        return $this;
    }
}

class StringField extends Field
{
    public ?string $placeholder = "";
    public bool $is_textarea = false;

    /** Defines the placeholder. Call the method without params to use the label as placeholder */
    function placeholder(string $text = null)
    {
        $this->placeholder = $text;
        return $this;
    }

    function area()
    {
        $this->is_textarea = true;
        return $this;
    }

    function check(string $msg = null)
    {
        if (!$this->test('/^[\w\sÀ-ÿ\p{P}-]*$/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function render(string $attrs = "")
    {
        if ($this->is_textarea) {
            return "<label for=\"$this->key\">$this->label</label>"
                . "<textarea name=\"$this->key\" id=\"$this->key\"" . ($this->valid() ? "" : " aria-invalid=true") . ">$this->value</textarea>";
        } else {
            $placeholder = $this->placeholder ?? $this->label;
            return parent::render("placeholder=\"$placeholder\" $attrs");
        }
    }

    function max_length(int $count, string $msg = null)
    {
        if ($this->should_test() && strlen($this->value) > $count) {
            $this->set_error($msg ?? "Trop long");
        }
        return $this;
    }

    function min_length(int $count, string $msg = null)
    {
        if ($this->should_test() && strlen($this->value) < $count) {
            $this->set_error($msg ?? "Trop court");
        }
        return $this;
    }
}

class SwitchField extends Field
{
    public string $true_label = "";
    public string $false_label = "";

    function check(string $msg = null)
    {
        if (!$this->test('/\D*$/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function set_labels(string $true_label, string $false_label)
    {
        $this->true_label = $true_label;
        $this->false_label = $false_label;
        return $this;
    }

    function get_label()
    {
        return $this->true_label;
    }

    function render(string $attrs = "")
    {
        return "<label for=\"$this->key\">"
            . "<input type=checkbox role=switch name=\"$this->key\" id=\"$this->key\" value=1 $attrs " . ($this->valid() ? "" : " aria-invalid=true") . ($this->value ? " checked" : "") . ">"
            . ($this->true_label && $this->false_label ?
                "<ins>$this->true_label <i class=\"fas fa-check\"></i></ins><del>$this->false_label <i class=\"fas fa-xmark\"></i></del>"
                : $this->label)
            . "</label>";
    }
}

class UploadField extends Field
{
    public string $target_dir = "uploads/";

    function check(string $msg = null)
    {
        if (isset($_FILES[$this->key])) {
            if ($_FILES[$this->key]["name"] != '') {
                $target_file = $this->target_dir . basename($_FILES[$this->key]["name"]);
                $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $this->check_file_exists($target_file);
                $this->check_file_size();
                $this->check_format($fileType);
            } else {
                $this->set_error("Choisissez un fichier");
            }

        }
    }

    function check_file_exists(string $target_file)
    {
        // Check if file already exists in the repo
        if (file_exists($target_file)) {
            $this->set_error("Le fichier existe déjà.");
        }
    }

    function check_file_size()
    {
        // Check file size
        if ($_FILES[$this->key]["size"] > 1000000) {
            $this->set_error("Fichier trop lourd.");
        }
    }

    function check_format(string $fileType)
    {
        // Allow certain file formats
        if (
            $fileType != "jpg" && $fileType != "png" && $fileType != "jpeg"
            && $fileType != "gif" && $fileType != "pdf"
        ) {
            $this->set_error("Seuls les formats JPG, PNG, JPEG, GIF et PDF sont acceptés.");
        }
    }

    function save_file()
    {
        $target_file = $this->target_dir . basename($_FILES[$this->key]["name"]);
        if (file_exists($target_file)) {
            if (move_uploaded_file($_FILES[$this->key]["tmp_name"], $target_file)) {
                return "Fichier modifié";
            }
        } else {
            if (move_uploaded_file($_FILES[$this->key]["tmp_name"], $target_file)) {
                return "Fichier enregistré";
            }
        }

    }

    function get_type()
    {
        return $_FILES[$this->key]["type"];
    }

    function get_size()
    {
        return $_FILES[$this->key]["size"];
    }

    function get_name()
    {
        return $_FILES[$this->key]["name"];
    }

    function set_target_dir(string $directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        $this->target_dir = $directory;
        return $this;
    }
}

class EmailField extends StringField
{
    function check(string $msg = null)
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->set_error($msg ?? "Format d'email invalide");
        }
    }
}

class PhoneField extends StringField
{
    function check(string $msg = null)
    {
        /** The regex now match for between 9 and 14 numbers with an optional + in the begining */
        if (!$this->test("/^[+]?(\d\s*?){9,14}$/")) {
            $this->set_error($msg ?? "Format de numéro de téléphone invalide");
        }
    }
}

class PasswordField extends StringField
{
    public bool $new = false;

    function check(string|null $msg = null)
    {
        return true;
    }

    function set_new()
    {
        $this->new = true;
        return $this;
    }

    function secure()
    {
        return $this->with_lowercase()->with_uppercase()->with_number()->min_length(8);
    }

    function with_number(string $msg = null)
    {
        if ($this->should_test() && !$this->test("/[0-9]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins un chiffre");
        }
        return $this;
    }

    function with_uppercase(string $msg = null)
    {
        if ($this->should_test() && !$this->test("/[A-Z]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins une lettre majuscule");
        }
        return $this;
    }

    function with_lowercase(string $msg = null)
    {
        if ($this->should_test() && !$this->test("/[a-z]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins une lettre minuscule");
        }
        return $this;
    }
}