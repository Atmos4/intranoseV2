<?php

class DateField extends Field
{
    public function set_type(): void
    {
        $this->type = FieldType::Date;
    }

    public function check(string $msg = null): void
    {
        if (!$this->test("/^[\d-]*$/")) {
            $this->set_error($msg ?? "Format invalide");
        }
    }

    /** Set upper date limit */
    public function max(?string $date, string $msg = null, bool $as_attr = false): static
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
    public function min(?string $date, string $msg = null, bool $as_attr = false): static
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
