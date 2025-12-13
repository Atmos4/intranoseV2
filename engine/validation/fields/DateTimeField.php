<?php
class DateTimeField extends Field
{
    function set_type(): void
    {
        $this->type = FieldType::DateTime;
    }

    function check(string $msg = null): void
    {
        if (!$this->test("/^[\d\- :T]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper date limit */
    function max(string|null $date, string $msg = null, bool $as_attr = false): static
    {
        if ($this->should_test() && $date && $this->value && strtotime($this->value) > strtotime($date)) {
            $this->set_error($msg ?? "Trop tard");
        }
        if ($as_attr && $date) {
            $this->attributes(["max" => $date]);
        }
        return $this;
    }

    /** Set lower date limit */
    function min(string|null $date, string $msg = null, bool $as_attr = false): static
    {
        if ($this->should_test() && $date && strtotime($this->value) < strtotime($date)) {
            $this->set_error($msg ?? "Trop tôt");
        }
        if ($as_attr && $date) {
            $this->attributes(["min" => $date]);
        }
        return $this;
    }
}