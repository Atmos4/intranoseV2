<?php

/** Simple validation class */
class Validator extends FieldBuilder
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

    public function valid(?string $key = null)
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

    public function render_validation(?string $prefix = null): string
    {
        $result = "";

        if ($prefix === null) {
            // Add form action name
            if ($this->action) {
                $result .= "<input type=\"hidden\" name=\"action\" value=\"$this->action\">";
            }

            // Add csrf
            $result .= set_csrf();
        }
        foreach ($this->fields as $key => $field) {
            if ($prefix !== null && $key !== $prefix && !str_starts_with($key, "{$prefix}[")) {
                continue;
            }
            if ($field->error) {
                $label = $field->get_label();
                $id = self::key_to_id($field->key);
                $result .= "<label for=\"{$id}\" class=\"error\">"
                    . ($label ? "{$field->get_label()} : " : "") . "$field->error</label>";
            }
        }
        if ($prefix === null && !$this->empty) {
            if ($this->error_msg) {
                $result .= "<label class=\"error\">$this->error_msg</label>";
            } elseif ($this->valid() && $this->success) {
                $result .= "<ins>$this->success</ins><br>";
            }
        }
        return $result;
    }

    /**
     * Checks that every registered field whose key is $prefix, or nested under it
     * (e.g. "activity[2]" matches "activity[2][name]"), is valid.
     */
    public function valid_prefix(string $prefix): bool
    {
        foreach ($this->fields as $key => $field) {
            if (($key === $prefix || str_starts_with($key, "{$prefix}[")) && !$field->valid()) {
                return false;
            }
        }
        return true;
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
    public static function key_to_id(string $key): string
    {
        return rtrim(preg_replace('/\[([^\]]*)\]/', '_$1', $key), '_');
    }

    /**
     * Resolves a bracket-notation key against a nested array.
     * e.g. "user[0][first_name]" against $_POST returns $_POST['user'][0]['first_name']
     */
    public static function resolve_nested_value(array $data, string $key): mixed
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
    public function create($key, $field, $msg)
    {
        $value = $this->empty ? (self::resolve_nested_value($this->nested_initial_values($key), $key) ?? null) : self::resolve_nested_value($_POST, $key);
        $this->fields[$key] = new $field($key, $value, $this);
        $this->fields[$key]->check($msg);
        return $this->fields[$key];
    }

    private function nested_initial_values(string $key): array
    {
        preg_match('/^[^\[]+/', $key, $m);
        $root = $m[0] ?? $key;
        return isset($this->fields[$root]) ? [$root => $this->fields[$root]->value] : [];
    }

    public function row_keys(string $prefix): array
    {
        $rows = self::resolve_nested_value($this->empty ? $this->nested_initial_values($prefix) : $_POST, $prefix);
        return is_array($rows) ? array_keys($rows) : [];
    }

    public function collection(string $key): FieldCollection
    {
        return new FieldCollection($this, $key);
    }
}
