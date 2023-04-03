<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


#[Entity, Table(name: 'categories')]
class Category
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[ManyToOne(targetEntity: Race::class, inversedBy: "categories")]
    public Race|null $race = null;

    #[Column]
    public string $name = "";

    #[Column]
    public bool $removed = false;

    /** @var Collection<int,RaceEntry> entries */
    #[OneToMany(targetEntity: RaceEntry::class, mappedBy: "category", cascade: ["remove"])]
    public Collection $entries;

    /** @param Collection<int,Category> categories */
    static function toSelectOptions($categories): array
    {
        $result = [];
        foreach ($categories as $category) {
            $result[$category->id] = $category->name;
        }
        return $result;
    }
}