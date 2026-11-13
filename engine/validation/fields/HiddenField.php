<?php

class HiddenField extends Field
{
    public string $autocomplete = "";

    protected function set_type(): void
    {
        $this->type = FieldType::Hidden;
    }

    public function render()
    {
        return $this->render_core();
    }
}
