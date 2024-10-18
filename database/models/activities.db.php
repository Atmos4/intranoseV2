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

    function toIcon()
    {
        return match ($this) {
            self::RACE => "fa-flag-checkered",
            self::TRAINING => "fa-dumbbell",
            self::OTHER => "fa-bowl-food",
        };
    }

    function toName()
    {
        return match ($this) {
            self::RACE => "Course",
            self::TRAINING => "Entrainement",
            self::OTHER => "Autre",
        };
    }
}

#[Entity, Table(name: 'activities')]
class Activity
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public ActivityType $type = ActivityType::RACE;

    #[Column]
    public DateTime $date;

    #[Column]
    public DateTime|null $deadline = null;

    #[Column]
    public bool $open = false;

    #[Column]
    public string $name;

    #[Column]
    public string $location_label = "";

    #[Column]
    public string $location_url = "";

    #[Column(type: "text")]
    public string $description = "";

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

    function set(string $name, string $date, string $location_label = "", string $location_url = "", string $description = "")
    {
        $this->name = $name;
        $this->date = date_create($date);
        $this->location_label = $location_label;
        $this->location_url = $location_url;
        $this->description = $description;
    }
}