<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity, Table(name: 'event_entries')]
class EventEntry
{
    #[Id, ManyToOne]
    public User|null $user = null;

    #[Id, ManyToOne]
    public Event|null $event = null;

    #[Column]
    public bool $present = false;

    #[Column]
    public bool $transport = false;

    #[Column]
    public bool $accomodation = false;

    #[Column]
    public DateTime $date;

    #[Column]
    public string $comment = "";

    function __construct()
    {
        $this->date = date_create();
    }

    function toForm()
    {
        return
            [
                "event_entry" => $this?->present ?? null,
                "event_transport" => $this?->transport ?? null,
                "event_accomodation" => $this?->accomodation ?? null,
                "event_comment" => $this?->comment ?? null,
            ];
    }

    function set(
        $user,
        $event,
        $present,
        $transport,
        $accomodation,
        $date,
        $comment
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->present = $present ?? false;
        $this->transport = $transport;
        $this->accomodation = $accomodation;
        $this->date = $date;
        $this->comment = $comment;
    }
}

#[Entity, Table(name: 'events')]
class Event
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $name = "";

    #[Column]
    public DateTime $start_date;

    #[Column]
    public DateTime $end_date;

    #[Column]
    public DateTime $deadline;

    #[Column(type: "text")]
    public string $description = "";

    #[Column]
    public bool $open = false;

    #[Column]
    public string $bulletin_url = "";

    #[Column]
    public bool $is_transport = True;

    #[Column]
    public bool $is_accomodation = True;

    #[Column(options: ["default" => "COMPLEX"])]
    public EventType $type = EventType::Complex;

    /** @var Collection<int, EventEntry> */
    #[OneToMany(targetEntity: EventEntry::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $entries;

    /** @var Collection<int, Activity> */
    #[OneToMany(targetEntity: Activity::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $activities;


    /** @var Collection<int, UserGroup> */
    #[ManyToMany(targetEntity: UserGroup::class, inversedBy: 'events')]
    public Collection $groups;

    #[OneToOne(targetEntity: Conversation::class, cascade: ["persist", "remove"])]
    public Conversation|null $conversation = null;

    function __construct()
    {
        //this is needed when creating a new Event
        $this->groups = new ArrayCollection();
        $this->start_date = date_create();
        $this->end_date = date_create();
        $this->deadline = date_create();
        $this->is_accomodation = True;
        $this->is_transport = True;
        $this->conversation = new Conversation();
    }

    function set(
        $name,
        $start_date,
        $end_date,
        $deadline,
        $bulletin_url
    ) {
        $this->name = $name;
        $this->start_date = date_create($start_date);
        $this->end_date = date_create($end_date);
        $this->deadline = date_create($deadline);
        $this->bulletin_url = $bulletin_url;
    }
}

enum EventType: string
{
    case Simple = 'SIMPLE';
    case Complex = 'COMPLEX';
}

class EventDto
{
    function __construct(
        public int $id,
        public string $name,
        public DateTime $start,
        public DateTime|null $end,
        public DateTime $deadline,
        public bool $open,
        /** @var UserGroup[] */
        public bool|null $registered,
    ) {
    }

    /** Used to transfer basic event data without graph
     *  @return EventDto[] */
    static function fromEventList(array $events)
    {
        $result = [];
        foreach ($events as $event) {

            $result[] = new EventDto(
                $event['id'],
                $event['name'],
                $event['start_date'],
                $event['end_date'],
                $event['deadline'],
                $event['open'],
                isset($event['present']) ? $event['present'] : null
            );
        }
        return $result;
    }
}