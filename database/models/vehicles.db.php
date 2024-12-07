<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'vehicles')]
class Vehicle
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $name;

    #[Column]
    public string $start_location = '';

    #[Column]
    public string $return_location = '';

    #[Column]
    public string $capacity;

    #[Column]
    public DateTime $start_date;

    #[Column]
    public DateTime $return_date;

    #[ManyToOne]
    public User|null $manager = null;

    #[ManyToOne]
    public Event $event;

    /** @var Collection<int, User> */
    #[ManyToMany(targetEntity: User::class)]
    public Collection $passengers;

}