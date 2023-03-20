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
    public bool $empty;
    public string|null $action;

    public function __construct(array $form_values, $action = null)
    {
        $this->action = $action;
        if (empty($_POST) and empty($_FILES)) {
            $this->empty = true;
        } elseif (!$action || $_POST['action'] == $action) {
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

    function render_errors()
    {
        $result = "";

        // Add form action name
        if ($this->action)
            $result = "<input type=\"hidden\" name=\"action\" value=\"$this->action\">";

        // Add csrf
        $result .= set_csrf();

        foreach ($this->fields as $field) {
            if ($field->error)
                $result .= "<label for=\"$field->key\" class=\"error\">{$field->get_label()} : $field->error</label>";
        }
        if ($result != "") {
            return $result . "<br/><br/>";
        }
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
    function text(string $key, string $msg = null): StringField
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

    /** Creates new date field */
    function switch (string $key, string $msg = null): SwitchField
    {
        $this->fields[$key] = new SwitchField($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    function upload(string $key, string $msg = null): UploadField
    {
        $this->fields[$key] = new UploadField($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    function email(string $key, string $msg = null): EmailField
    {
        $this->fields[$key] = new EmailField($key, $this->value($key), $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    function phone(string $key, string $msg = null): PhoneField
    {
        $this->fields[$key] = new PhoneField($key, $this->value($key), $this);
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
        return "<label for=\"$this->key\">$this->label<input $attrs name=\"$this->key\" id=\"$this->key\" value=\"$this->value\" " . ($this->valid() ? "" : " aria-invalid=true") . "></label>";
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
        return $this->context?->text($key, $msg) ?? new StringField($key);
    }
    /** Enables Decorator pattern */
    function date(string $key, string $msg = "")
    {
        return $this->context?->date($key, $msg) ?? new DateField($key);
    }

    function upload(string $key, string $msg = "")
    {
        return $this->context?->upload($key, $msg) ?? new UploadField($key);
    }

    function email(string $key, string $msg = "")
    {
        return $this->context?->email($key, $msg) ?? new EmailField($key);
    }

    function phone(string $key, string $msg = "")
    {
        return $this->context?->phone($key, $msg) ?? new PhoneField($key);
    }

    function check(string $msg = null)
    {
    }
}

class NumberField extends Field
{
    function check(string $msg = null)
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
    function check(string $msg = null)
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
        if (!preg_match('/^[\w\sÀ-ÿ\p{P}-]*$/', $this->value)) {
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
            return parent::render("type = text placeholder=\"$placeholder\"");
        }
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

class SwitchField extends Field
{
    public string $true_label = "";
    public string $false_label = "";

    function check(string $msg = null)
    {
        if (!preg_match('/^\d*$/', $this->value)) {
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

class TextAreaField extends Field
{
    function check(string $msg = null)
    {
        if (!preg_match('/^[\w\sÀ-ÿ\p{P}-]*$/', $this->value)) {
            $this->set_error($msg ?? "Format invalide");
        }
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

    function render(string $attrs = "")
    {
        return parent::render("type = file");
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

class EmailField extends Field
{
    public ?string $placeholder = "";

    /** Defines the placeholder. Call the method without params to use the label as placeholder */
    function placeholder(string $text = null)
    {
        $this->placeholder = $text;
        return $this;
    }

    function check(string $msg = null)
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->set_error($msg ?? "Format d'email invalide");
        }
    }

    function render(string $attrs = "")
    {
        $placeholder = $this->placeholder ?? $this->label;
        return parent::render("type =\"email\" placeholder=\"$placeholder\"");
    }
}

class PhoneField extends Field
{
    public ?string $placeholder = "";

    /** Defines the placeholder. Call the method without params to use the label as placeholder */
    function placeholder(string $text = null)
    {
        $this->placeholder = $text;
        return $this;
    }

    function check(string $msg = null)
    {
        if (!preg_match('/^[0-9]{10}+$/', $this->value)) {
            $this->set_error($msg ?? "Format de numéro de téléphone invalide");
        }
    }

    function render(string $attrs = "")
    {
        $placeholder = $this->placeholder ?? $this->label;
        return parent::render("type =\"tel\" placeholder=\"$placeholder\"");
    }
}



/*
class RadioField extends Field
{
public string $checked_id = 0;
function set_text_list(array $list)
{
$this->text_list = $list;
return $this;
}
function set_checked_id(int $checked_id)
{
return $this->checked_id = $checked_id;
}
function render(string $attrs = "")
{
$render_string = "<fieldset>";
foreach($text_list as $text){
$render_string .= "<legend>Sexe</legend>
<label for="homme">
<input type="radio" id="homme" name="sexe" value="H" <?php echo ($user->gender == Gender::M) ? 'checked="checked"' : ''; ?> disabled>
Homme
</label>";
}
return "<fieldset>"
. (foreach ($text_list as $text) {})."
<legend>Sexe</legend>
<label for="homme">
<input type="radio" id="homme" name="sexe" value="H" <?php echo ($user->gender == Gender::M) ? 'checked="checked"' : ''; ?> disabled>
Homme
</label>
<label for="dame">
<input type="radio" id="dame" name="sexe" value="D" <?php echo ($user->gender == Gender::W) ? 'checked="checked"' : ''; ?> disabled>
Dame
</label>
</fieldset>";
}
}
*/