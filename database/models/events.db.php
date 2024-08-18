<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

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

    /** @var Collection<int, EventEntry> */
    #[OneToMany(targetEntity: EventEntry::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $entries;

    /** @var Collection<int, Race> */
    #[OneToMany(targetEntity: Race::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $races;

    /** @var Collection<int, Activity> */
    #[OneToMany(targetEntity: Activity::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $activities;

    function __construct()
    {
        $this->start_date = date_create();
        $this->end_date = date_create();
        $this->deadline = date_create();
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

enum EventType
{
    case Event;
    case Activity;
}

class EventDto
{
    function __construct(
        public EventType $type,
        public int $id,
        public string $name,
        public DateTime $start,
        public DateTime|null $end,
        public DateTime $deadline,
        public bool $open,
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
                EventType::Event,
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

    /** Used to transfer basic event data without graph
     *  @return EventDto[] */
    static function fromActivityList(array $activities)
    {
        $result = [];
        foreach ($activities as $activity) {

            $result[] = new EventDto(
                EventType::Activity,
                $activity['id'],
                $activity['name'],
                $activity['date'],
                null,
                $activity['deadline'],
                $activity['open'],
                isset($activity['present']) ? $activity['present'] : null
            );
        }
        return $result;
    }
}