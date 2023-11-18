<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'race_entries')]
class RaceEntry
{
    #[Id, ManyToOne]
    public User|null $user = null;

    #[Id, ManyToOne]
    public Race|null $race = null;

    #[ManyToOne]
    public Category|null $category = null;

    #[Column]
    public bool $present = false;

    #[Column]
    public string $comment = "";

    function set($user, $race, $present, $comment)
    {
        $this->user = $user;
        $this->race = $race;
        $this->present = $present;
        $this->comment = $comment;
    }
}

#[Entity, Table(name: 'races')]
class Race
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public DateTime $date;

    #[Column]
    public string $name;

    #[Column]
    public string $place;

    #[ManyToOne]
    public Event|null $event = null;

    #[OneToMany(targetEntity: RaceEntry::class, mappedBy: "race", cascade: ["remove"])]
    public Collection $entries;

    /** @var Collection<int, Category> categories */
    #[OneToMany(targetEntity: Category::class, mappedBy: "race", cascade: ["persist", "remove"])]
    public Collection $categories;

    function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    function set(string $name, DateTime $date, string $place, Event $event)
    {
        $this->name = $name;
        $this->place = $place;
        $this->date = $date;
        $this->event = $event;
    }
}