<?php
class FieldRow extends FieldBuilder
{
    public function __construct(protected Validator $v, protected string $prefix)
    {
    }
    protected function create(string $rel, string $class, ?string $msg): Field
    {
        return $this->v->create("{$this->prefix}[$rel]", $class, $msg);   // concrete field
    }
    public function field(string $rel): Field
    {
        return $this->v->get_field("{$this->prefix}[$rel]");
    }
    public function collection(string $rel): FieldCollection
    {
        return new FieldCollection($this->v, "{$this->prefix}[$rel]");
    }
    public function valid(): bool
    {
        return $this->v->valid_prefix($this->prefix);
    }
    public function render_validation(): string
    {
        return $this->v->render_validation($this->prefix);
    }
}