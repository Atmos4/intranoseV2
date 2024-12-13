<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'clubs')]
class Club
{
    #[Id, Column(unique: true)]
    public string $slug; // intranose

    #[Column]
    public string $name; // Intranose

    function __construct($name, $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
    }

    function toForm()
    {
        return [
            "name" => $this->name,
            "slug" => $this->slug,
        ];
    }
}