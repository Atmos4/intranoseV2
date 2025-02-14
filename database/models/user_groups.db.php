<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'user_groups')]
class UserGroup
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $name = "";

    /** @var Collection<int, User> */
    #[ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    public Collection $members;

    /** @var Collection<int, Event> */
    #[ManyToMany(targetEntity: Event::class, mappedBy: 'groups')]
    public Collection $events;

    #[Column]
    public ThemeColor $color;
}