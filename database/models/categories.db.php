<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'category_sets')]
class CategorySet
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public bool $reusable = false;

    #[Column]
    public string $name = "";

    /** @var Collection<int, Category> */
    #[OneToMany(targetEntity: Category::class, mappedBy: 'categorySet')]
    public Collection $categories;
}

#[Entity]
class Category
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[ManyToOne]
    public CategorySet|null $categorySet = null;

    #[Column]
    public string $name = "";
}