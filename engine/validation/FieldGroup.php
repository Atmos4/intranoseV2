<?php
class FieldGroup implements IteratorAggregate, Countable
{
    public function __construct(private array $fields)
    {
    }

    public function __call(string $name, array $args): static
    {
        foreach ($this->fields as $f) {
            if (!method_exists($f, $name))
                throw new BadMethodCallException("$name() does not exist on " . $f::class);
            $f->$name(...$args);
        }
        return $this;
    }
    public function out(): never
    {
        throw new LogicException("out() is not supported on a field group");
    }
    public function fields(): array
    {
        return $this->fields;
    }
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->fields);
    }
    public function count(): int
    {
        return count($this->fields);
    }
}
