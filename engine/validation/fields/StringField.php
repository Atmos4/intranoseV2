<?php

class StringField extends Field
{
    public ?string $placeholder = "";

    /** Defines the placeholder. Call the method without params to use the label as placeholder */
    public function placeholder(string $text = null): static
    {
        $this->placeholder = $text;
        return $this;
    }

    public function check(string $msg = null): void
    {
        if (!$this->test('/^[\w\sÀ-ÿ\p{P}-]*$/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    public function render()
    {
        $this->attributes(["placeholder" => $this->placeholder ?? $this->label]);
        return parent::render();
    }

    public function __tostring()
    {
        return $this->render();
    }

    public function max_length(int $count, string $msg = null): static
    {
        if ($this->should_test() && strlen($this->value) > $count) {
            $this->set_error($msg ?? "Trop long");
        }
        return $this;
    }

    public function min_length(int $count, string $msg = null): static
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
    public function check(string $msg = null): void
    {
        if ($this->test('/<script>/')) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    public function render()
    {
        $this->attributes(["placeholder" => $this->placeholder ?? $this->label]);
        $result = "<textarea {$this->props(false)}>$this->value</textarea>";
        return $this->render_label($result);
    }
}

class NumberField extends StringField
{
    public function set_type(): void
    {
        $this->type = FieldType::Number;
    }

    public function check(string $msg = null): void
    {
        if (!$this->test("/^[\d]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper limit for the number field */
    public function max(int $count, string $msg = null): static
    {
        if ($this->should_test() && $this->value > $count) {
            $this->set_error($msg ?? "Trop grand");
        }
        return $this;
    }

    /** Set lower limit for the number field */
    public function min(int $count, string $msg = null): static
    {
        if ($this->should_test() && $this->value < $count) {
            $this->set_error($msg ?? "Trop petit");
        }
        return $this;
    }
}

class EmailField extends StringField
{
    public function set_type(): void
    {
        $this->type = FieldType::Email;
    }

    public function check(string $msg = null): void
    {
        if ($this->should_test() && $this->value && !filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->set_error($msg ?? "Format d'email invalide");
        }
    }
}

class PhoneField extends StringField
{
    public function set_type(): void
    {
        $this->type = FieldType::Phone;
    }

    public function check(string $msg = null): void
    {
        /** The regex now match for between 9 and 14 numbers with an optional + in the begining */
        if ($this->should_test() && $this->required && !$this->test("/^[+]?(\d\s*?){9,14}$/")) {
            $this->set_error($msg ?? "Format de numéro de téléphone invalide");
        }
    }
}

class UrlField extends StringField
{
    public function set_type(): void
    {
        $this->type = FieldType::Url;
    }

    public function check(string $msg = null): void
    {
        if ($this->should_test() && $this->value && !filter_var($this->value, FILTER_VALIDATE_URL)) {
            $this->set_error("Format de l'url invalide");
        }
    }
}

class PasswordField extends StringField
{
    public function set_type(): void
    {
        $this->type = FieldType::Password;
    }

    public function check(?string $msg = null): void {}

    public function secure(): static
    {
        return $this->with_lowercase()->with_uppercase()->with_number()->min_length(8);
    }

    public function with_number(string $msg = null): static
    {
        if ($this->should_test() && !$this->test("/[0-9]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins un chiffre");
        }
        return $this;
    }

    public function with_uppercase(string $msg = null): static
    {
        if ($this->should_test() && !$this->test("/[A-Z]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins une lettre majuscule");
        }
        return $this;
    }

    public function with_lowercase(string $msg = null): static
    {
        if ($this->should_test() && !$this->test("/[a-z]+/")) {
            $this->set_error($msg ?? "Doit contenir au moins une lettre minuscule");
        }
        return $this;
    }
}
