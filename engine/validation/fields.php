<?php

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
    case Url = "url";
}

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

    private array $attributes = [];

    function __construct(string $key, mixed $value = null, $context = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->context = $context;
        $this->error = null;
        $this->label = "";
        $this->set_type();
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

    function attributes(array $attrs)
    {
        $this->attributes = array_merge($this->attributes, $attrs);
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

    protected function render_core()
    {
        return "<input {$this->props()}>";
    }

    function props(bool $includeValue = true)
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

    protected function render_label($input_render, $reverse = false)
    {
        if ($this->label) {
            $label_content = $reverse ? $input_render . $this->label : $this->label . $input_render;
            $input_render = "<label {$this->render_attrs()} for=\"$this->key\">{$label_content}</label>";
        }
        return $input_render;
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

    function readonly()
    {
        $this->readonly = true;
        return $this;
    }

    protected function test(string $preg, $value = null)
    {
        return preg_match($preg, $value ?? $this->value ?? "");
    }

    /** Makes the field required */
    function required(string $msg = null)
    {
        $this->required = true;
        if ($this->should_test() && !$this->value) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }

    function check(string $msg = null)
    {
    }

    protected function set_type()
    {
        $this->type = FieldType::Text;
    }

    /** Add a condition that if false will set the according error message */
    function condition(bool $condition, string $error_msg)
    {
        if ($this->should_test() and !$condition) {
            $this->set_error($error_msg);
        }
        return $this;
    }

    /** See more information: https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/autocomplete */
    function autocomplete($autocomplete = "")
    {
        $this->autocomplete = $autocomplete;
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

    function check(string $msg = null)
    {
        if (!$this->test('/^[\w\sÀ-ÿ\p{P}-]*$/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function render()
    {
        $this->attributes(["placeholder" => $this->placeholder ?? $this->label]);
        return parent::render();
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
        if ($this->should_test() && strlen($this->value ?? "") < $count) {
            $this->set_error($msg ?? "Trop court");
        }
        return $this;
    }
}

class TextAreaField extends StringField
{
    // budget xss protection
    function check(string $msg = null)
    {
        if ($this->test('/<script>/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    function render()
    {
        $this->attributes(["placeholder" => $this->placeholder ?? $this->label]);
        $result = "<textarea {$this->props(false)}>$this->value</textarea>";
        return $this->render_label($result);
    }
}

class NumberField extends StringField
{
    function set_type()
    {
        $this->type = FieldType::Number;
    }

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
    function set_type()
    {
        $this->type = FieldType::Date;
    }

    function check(string $msg = null)
    {
        if (!$this->test("/^[\d-]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper date limit */
    function max(string|null $date, string $msg = null)
    {
        if ($this->should_test() && $date && strtotime($this->value) > strtotime($date)) {
            $this->set_error($msg ?? "Trop tard");
        }
        return $this;
    }

    /** Set lower date limit */
    function min(string|null $date, string $msg = null)
    {
        if ($this->should_test() && $date && strtotime($this->value) < strtotime($date)) {
            $this->set_error($msg ?? "Trop tôt");
        }
        return $this;
    }
}

class SwitchField extends Field
{
    function set_type()
    {
        $this->type = FieldType::Checkbox;
    }

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


class UploadField extends Field
{
    public static $FILE_MIME = [
        'image' => [
            'image/webp',
            'image/tiff',
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/x-ms-bmp',
            'image/x-bmp',
            'image/x-portable-bitmap',
            'image/vnd.adobe.photoshop',
            'image/x-eps',
            'application/postscript',
            'application/dicom',
            'application/pcx',
            'application/x-pcx',
            'image/pcx',
            'image/x-pc-paintbrush',
            'image/x-pcx',
            'zz-application/zz-winassoc-pcx',
            'image/jp2',
            'image/heif'
        ],
        'doc' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vnd.ms-word.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.text',
        ],
        'pdf' => ['application/pdf'],
        'excel' => [
            'application/vnd.ms-excel',
            'application/x-ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.spreadsheet',
        ],
        'powerpoint' => [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.oasis.opendocument.presentation',
        ]
    ];
    public static $IMAGE_MIME = [
        'image' => [
            'image/webp',
            'image/tiff',
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/x-ms-bmp',
            'image/x-bmp',
            'image/x-portable-bitmap',
            'image/vnd.adobe.photoshop',
            'image/x-eps',
            'application/postscript',
            'application/dicom',
            'application/pcx',
            'application/x-pcx',
            'image/pcx',
            'image/x-pc-paintbrush',
            'image/x-pcx',
            'zz-application/zz-winassoc-pcx',
            'image/jp2',
            'image/heif'
        ]
    ];
    function set_type()
    {
        $this->type = FieldType::File;
    }

    public string $target_dir = "/uploads/";
    public string $target_file = '';
    public string $file_type;
    public string $file_name = '';

    public array $allowed_mime = array();

    function __construct(string $key, mixed $value = null, $context = null)
    {
        parent::__construct($key, $value, $context);
        $this->file_name = $_FILES[$this->key]["name"] ?? "";
        $this->target_dir = app_path() . $this->target_dir;
        $this->target_file = isset($_FILES[$this->key]) ? $this->target_dir . basename($_FILES[$this->key]["name"]) : "";
        $this->file_type = isset($_FILES[$this->key]) ? strtolower(pathinfo($this->target_file, PATHINFO_EXTENSION)) : "";
    }

    function required(string $msg = null)
    {
        if ($this->should_test() && !$this->file_name) {
            $this->set_error($msg ?? "Requis");
        }
        return $this;
    }

    function check(string $msg = null)
    {
        if (isset($_FILES[$this->key])) {
            if ($_FILES[$this->key]["name"] != '') {
                // Check if the error field is ok 
                if (
                    !isset($_FILES[$this->key]['error']) ||
                    is_array($_FILES[$this->key]['error'])
                ) {
                    $this->set_error('Paramètres incorrects');
                } else {
                    // Check $_FILES['upfile']['error'] value.
                    switch ($_FILES[$this->key]['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $this->set_error("Choisissez un fichier");
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                            $this->set_error("Fichier trop lourd");
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $this->set_error("Fichier trop lourd");
                            break;
                        default:
                            $this->set_error("Erreur inconnue");
                    }

                    // Check custom filesize here. 
                    if ($_FILES[$this->key]['size'] > 1000000) {
                        $this->set_error('Fichier trop lourd - ' . round($_FILES[$this->key]['size'] / 1000000, 2) . 'MB');
                    }
                }

            }
        }
    }

    function mime(array $mimes)
    {
        if ($this->should_test()) {
            $this->allowed_mime = $mimes;
            // Allow certain file formats
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            // Flatten the array
            $flatArray = array_reduce($this->allowed_mime, 'array_merge', []);
            if (
                !in_array(
                    $finfo->file($_FILES[$this->key]['tmp_name']),
                    $flatArray,
                    true
                )
            ) {
                $this->set_error("Seuls les formats " . implode(", ", array_keys($this->allowed_mime)) . " sont acceptés");
            }
        }
        return $this;
    }

    function max_size(int $size)
    {
        // Check custom filesize here. 
        if ($this->should_test() && $_FILES[$this->key]['size'] > $size) {
            $this->set_error('Fichier trop lourd - ' . round($_FILES[$this->key]['size'] / 1000000, 2) . 'MB');
        }
        return $this;
    }


    function save_file()
    {
        $file_exists = file_exists($this->target_file);
        if ($file_exists)
            unlink($this->target_file);
        $result = move_uploaded_file($_FILES[$this->key]["tmp_name"], $this->target_file);
        if ($result)
            Toast::success($file_exists ? "Fichier modifié" : "Fichier enregistré");
        else
            Toast::error("Problème à l'enregistrement");
        return $result;
    }

    function set_file_name(string $name)
    {
        $this->file_name = $name;
        $this->target_file = $this->target_dir . $name;
        return $this;
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
    function set_type()
    {
        $this->type = FieldType::Email;
    }

    function check(string $msg = null)
    {
        if ($this->should_test() && $this->value && !filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->set_error($msg ?? "Format d'email invalide");
        }
    }
}

class PhoneField extends StringField
{
    function set_type()
    {
        $this->type = FieldType::Phone;
    }

    function check(string $msg = null)
    {
        /** The regex now match for between 9 and 14 numbers with an optional + in the begining */
        if ($this->should_test() && $this->required && !$this->test("/^[+]?(\d\s*?){9,14}$/")) {
            $this->set_error($msg ?? "Format de numéro de téléphone invalide");
        }
    }
}

class PasswordField extends StringField
{
    function set_type()
    {
        $this->type = FieldType::Password;
    }

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

class SelectFieldOption
{
    public string $value = "";
    public string $label = "";
    public bool $selected = false;
}

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

    function option($value, $label)
    {
        $option = new SelectFieldOption();
        $option->value = $value;
        $option->label = $label;
        $option->selected = $this->value == $value;
        $this->options[] = $option;
        return $this;
    }

    function options(array $options)
    {
        foreach ($options as $value => $label) {
            $this->option($value, $label);
        }
        return $this;
    }
}

class UrlField extends StringField
{
    function set_type()
    {
        $this->type = FieldType::Url;
    }

    function check(string $msg = null)
    {
        if ($this->should_test() && $this->value && !filter_var($this->value, FILTER_VALIDATE_URL)) {
            $this->set_error("Format de l'url invalide");
        }
    }
}