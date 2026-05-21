<?php
class StringField extends Field
{
    public ?string $placeholder = "";

    /** Defines the placeholder. Call the method without params to use the label as placeholder */
    function placeholder(string $text = null): static
    {
        $this->placeholder = $text;
        return $this;
    }

    function check(string $msg = null): void
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

    function __tostring()
    {
        return $this->render();
    }

    function max_length(int $count, string $msg = null): static
    {
        if ($this->should_test() && strlen($this->value) > $count) {
            $this->set_error($msg ?? "Trop long");
        }
        return $this;
    }

    function min_length(int $count, string $msg = null): static
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
    function check(string $msg = null): void
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

class RichTextField extends TextAreaField
{
    public int $min_height = 200;
    public bool $enable_images = true;
    public bool $enable_links = true;
    public bool $enable_code = false;
    public array $toolbar_options = [];
    public ?string $session_id = null;

    function min_height(int $height): static
    {
        $this->min_height = $height;
        return $this;
    }

    function enable_images(bool $enable = true): static
    {
        $this->enable_images = $enable;
        return $this;
    }

    function enable_links(bool $enable = true): static
    {
        $this->enable_links = $enable;
        return $this;
    }

    function enable_code(bool $enable = true): static
    {
        $this->enable_code = $enable;
        return $this;
    }

    function toolbar(array $options): static
    {
        $this->toolbar_options = $options;
        return $this;
    }

    function session_id(string $id): static
    {
        $this->session_id = $id;
        return $this;
    }

    function check(string $msg = null): void
    {
        // More lenient validation for rich text HTML
        // Strip allowed HTML tags and then check for script tags
        $stripped = strip_tags($this->value ?? '', '<p><br><strong><em><u><s><ol><ul><li><blockquote><pre><code><a><img><h1><h2><h3>');
        if (preg_match('/<script>/i', $this->value ?? '')) {
            $this->set_error($msg ?? "Format invalide - scripts non autorisés");
        }
    }

    function render()
    {
        $this->attributes([
            "placeholder" => $this->placeholder ?? $this->label,
            "style" => "display:none"
        ]);

        // Build toolbar configuration
        $toolbar = $this->toolbar_options ?: $this->buildDefaultToolbar();
        $toolbar_json = htmlspecialchars(json_encode($toolbar), ENT_QUOTES, 'UTF-8');

        // Generate unique ID for this editor instance
        $editor_id = 'editor-' . uniqid();
        $textarea_id = $this->key . '_textarea';

        // Build textarea attributes manually to avoid duplicate IDs
        $v = htmlspecialchars($this->value ?? '', ENT_QUOTES, 'UTF-8');
        $textarea_attrs = "name=\"{$this->key}\" id=\"{$textarea_id}\""
            . ($this->valid() ? "" : " aria-invalid=\"true\" autofocus")
            . ($this->autocomplete ? " autocomplete=\"{$this->autocomplete}\"" : "")
            . ($this->disabled ? " disabled" : "")
            . ($this->required ? " required" : "")
            . ($this->readonly ? " readonly" : "")
            . $this->render_attrs();

        $textarea = "<textarea {$textarea_attrs}>{$v}</textarea>";
        $editor = "<div class='editor-container' id='{$editor_id}' style='background: var(--pico-background-color); min-height: {$this->min_height}px; border: 1px solid var(--pico-muted-border-color); border-radius: 4px;'></div>";

        $config = [
            'enableImages' => $this->enable_images,
            'enableLinks' => $this->enable_links,
            'enableCode' => $this->enable_code,
            'toolbar' => $toolbar
        ];

        // Add session ID if provided (for organizing uploaded images)
        if ($this->session_id) {
            $config['sessionId'] = $this->session_id;
        }

        $config_json = htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8');

        $wrapper = "<div class='richtext-editor' data-editor-id='{$editor_id}' data-textarea-id='{$textarea_id}' data-config='{$config_json}'>{$editor}{$textarea}</div>";

        // Custom render with label (help attribute handled by parent class if needed)
        if ($this->label) {
            return "<label for=\"{$textarea_id}\">{$this->label}{$wrapper}</label>";
        }

        return $wrapper;
    }

    private function buildDefaultToolbar(): array
    {
        $toolbar = [
            ['bold', 'italic', 'underline', 'strike']
        ];

        if ($this->enable_links) {
            $toolbar[] = ['blockquote', 'code-block'];
        }

        $toolbar[] = [['list' => 'ordered'], ['list' => 'bullet']];
        $toolbar[] = [['header' => [1, 2, 3, false]]];

        $buttons = [];
        if ($this->enable_links) {
            $buttons[] = 'link';
        }
        if ($this->enable_images) {
            $buttons[] = 'image';
        }
        if (!empty($buttons)) {
            $toolbar[] = $buttons;
        }

        $toolbar[] = ['clean'];

        return $toolbar;
    }
}

class NumberField extends StringField
{
    function set_type(): void
    {
        $this->type = FieldType::Number;
    }

    function check(string $msg = null): void
    {
        if (!$this->test("/^[\d]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper limit for the number field */
    function max(int $count, string $msg = null): static
    {
        if ($this->should_test() && $this->value > $count) {
            $this->set_error($msg ?? "Trop grand");
        }
        return $this;
    }

    /** Set lower limit for the number field */
    function min(int $count, string $msg = null): static
    {
        if ($this->should_test() && $this->value < $count) {
            $this->set_error($msg ?? "Trop petit");
        }
        return $this;
    }
}

class EmailField extends StringField
{
    function set_type(): void
    {
        $this->type = FieldType::Email;
    }

    function check(string $msg = null): void
    {
        if ($this->should_test() && $this->value && !filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->set_error($msg ?? "Format d'email invalide");
        }
    }
}

class PhoneField extends StringField
{
    function set_type(): void
    {
        $this->type = FieldType::Phone;
    }

    function check(string $msg = null): void
    {
        /** The regex now match for between 9 and 14 numbers with an optional + in the begining */
        if ($this->should_test() && $this->required && !$this->test("/^[+]?(\d\s*?){9,14}$/")) {
            $this->set_error($msg ?? "Format de numéro de téléphone invalide");
        }
    }
}

class UrlField extends StringField
{
    function set_type(): void
    {
        $this->type = FieldType::Url;
    }

    function check(string $msg = null): void
    {
        if ($this->should_test() && $this->value && !filter_var($this->value, FILTER_VALIDATE_URL)) {
            $this->set_error("Format de l'url invalide");
        }
    }
}

class PasswordField extends StringField
{
    function set_type(): void
    {
        $this->type = FieldType::Password;
    }

    function check(string|null $msg = null): void
    {
    }

    function secure(): static
    {
        return $this->with_lowercase()->with_uppercase()->with_number()->min_length(8);
    }

    function with_number(string $msg = null): static
    {
        if ($this->should_test() && !$this->test("/[0-9]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins un chiffre");
        }
        return $this;
    }

    function with_uppercase(string $msg = null): static
    {
        if ($this->should_test() && !$this->test("/[A-Z]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins une lettre majuscule");
        }
        return $this;
    }

    function with_lowercase(string $msg = null): static
    {
        if ($this->should_test() && !$this->test("/[a-z]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins une lettre minuscule");
        }
        return $this;
    }
}