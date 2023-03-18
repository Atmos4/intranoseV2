<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
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
    #[Id, ManyToOne(targetEntity: User::class)]
    public User|null $user = null;

    #[Id, ManyToOne(targetEntity: Event::class)]
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

    function to_form()
    {
        return
            [
                "event_entry" => $this?->present ?? null,
                "event_transport" => $this?->transport ?? null,
                "event_accomodation" => $this?->accomodation ?? null,
                "event_comment" => $this?->comment ?? null,
            ];
    }
}

#[Entity(repositoryClass: EventRepository::class), Table(name: 'events')]
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

    #[Column]
    public bool $open = false;

    /** @var Collection<int, EventEntry> */
    #[OneToMany(targetEntity: EventEntry::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $entries;

    /** @var Collection<int, Race> */
    #[OneToMany(targetEntity: Race::class, mappedBy: 'event', cascade: ["remove"])]
    public Collection $races;

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
        $deadline
    )
    {

        $this->name = $name;
        $this->start_date = date_create($start_date);
        $this->end_date = date_create($end_date);
        $this->deadline = date_create($deadline);
    }
}

class EventRepository extends EntityRepository
{

    /** @return EventDto[] */
    function listAllOpen($user_id)
    {
        $events = $this->getEntityManager()
            ->createQuery("SELECT ev.id, ev.name, ev.start_date, ev.end_date, ev.deadline, ev.open, en.present FROM Event ev" .
                " LEFT JOIN ev.entries en LEFT JOIN en.user u" .
                " WHERE ev.open = 1 AND (u.id IS NULL OR u.id = ?1)" .
                " ORDER BY ev.start_date DESC")
            ->setParameter(1, $user_id)
            ->getArrayResult();

        return EventDto::fromEventList($events);
    }

    function getById($event_id, $user_id = 0)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT ev, en FROM Event ev LEFT JOIN ev.entries en LEFT JOIN en.user u" .
                " WHERE ev.id = :eid AND (u.id IS NULL OR u.id = :uid)")
            ->setParameters(['eid' => $event_id, 'uid' => $user_id])
            ->getSingleResult();
    }

    function listDrafts()
    {
        $events = $this->getEntityManager()
            ->createQuery("SELECT ev.id, ev.name, ev.start_date, ev.end_date, ev.deadline, ev.open FROM Event ev" .
                " WHERE ev.open = 0" .
                " ORDER BY ev.start_date DESC")
            ->getArrayResult();
        return EventDto::fromEventList($events);
    }
}

// class EventEntryDto
// {
//     public bool $transport = false;
//     public bool $accomodation = false;
//     public string $comment = "";

//     function __construct($transport, $accomodation, $comment)
//     {
//         $this->transport = $transport;
//         $this->accomodation = $accomodation;
//         $this->comment = $comment;
//     }
// }

class EventDto
{
    public int $id;
    public string $name;
    public DateTime $start;
    public DateTime $end;
    public DateTime $deadline;
    public bool $open;
    public bool|null $registered;
    // public EventEntryDto|null $entry = null;

    function __construct(
        $id,
        $name,
        $start,
        $end,
        $deadline,
        $open,
        $registered,
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->deadline = $deadline;
        $this->open = $open;
        $this->registered = $registered;
    }

    /** @return EventDto[] */
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