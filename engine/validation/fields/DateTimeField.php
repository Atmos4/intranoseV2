<?php
class DateTimeField extends Field
{
    function set_type(): void
    {
        $this->type = FieldType::DateTime;
    }

    private function normalize(?string $date): ?string
    {
        if (!$date)
            return $date;
        return preg_replace('/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})(:\d{2})?/', '$1T$2', $date);
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
            $this->attributes(["max" => $this->normalize($date)]);
        }
        return $this;
    }

    /** Set lower date limit */
    function min(string|null $date, string $msg = null, bool $as_attr = false): static
    {
        if ($this->should_test() && $date && $this->value && strtotime($this->value) < strtotime($date)) {
            $this->set_error($msg ?? "Trop tôt");
        }
        if ($as_attr && $date) {
            $this->attributes(["min" => $this->normalize($date)]);
        }
        return $this;
    }

    protected function render_core(): string
    {
        $original = $this->value;
        $this->value = $this->normalize($this->value);
        $result = parent::render_core();
        $this->value = $original;
        return $result;
    }
}