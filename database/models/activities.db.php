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

#[Entity, Table(name: 'activity_entries')]
class ActivityEntry
{
    #[Id, ManyToOne]
    public User|null $user = null;

    #[Id, ManyToOne]
    public Activity|null $activity = null;

    #[ManyToOne]
    public Category|null $category = null;

    #[Column]
    public bool $present = false;

    #[Column]
    public string $comment = "";

    function set($user, $activity, $present, $comment)
    {
        $this->user = $user;
        $this->activity = $activity;
        $this->present = $present;
        $this->comment = $comment;
    }
}

enum ActivityType: string
{
    case RACE = "RACE";
    case TRAINING = "TRAINING";
    case OTHER = "OTHER";
}

#[Entity, Table(name: 'activities')]
class Activity
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public ActivityType $type;

    #[Column]
    public DateTime $date;

    #[Column]
    public string $name;

    #[Column]
    public string $location_label;

    #[Column]
    public string $location_url;

    #[Column]
    public string $description;

    #[ManyToOne]
    public Event|null $event = null;

    #[OneToMany(targetEntity: ActivityEntry::class, mappedBy: "activity", cascade: ["remove"])]
    public Collection $entries;

    /** @var Collection<int, Category> categories */
    #[OneToMany(targetEntity: Category::class, mappedBy: "activity", cascade: ["persist", "remove"])]
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