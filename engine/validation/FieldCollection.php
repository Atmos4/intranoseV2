<?php
class FieldCollection extends FieldBuilder implements IteratorAggregate, Countable
{
    public function __construct(protected Validator $v, protected string $prefix)
    {
    }
    protected function create(string $rel, string $class, ?string $msg): FieldGroup
    {
        $fields = [];
        foreach ($this->v->row_keys($this->prefix) as $i) {
            $fields[$i] = $this->v->create("{$this->prefix}[$i][$rel]", $class, $msg);
        }
        return new FieldGroup($fields);
    }
    public function getIterator(): Generator
    {
        foreach ($this->v->row_keys($this->prefix) as $i)
            yield $i => new FieldRow($this->v, "{$this->prefix}[$i]");
    }
    public function count(): int
    {
        return count($this->v->row_keys($this->prefix));
    }
    public function each(callable $fn): static
    {
        foreach ($this as $i => $row)
            $fn($row, $i);
        return $this;
    }
}
