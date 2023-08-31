<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

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

    static function getWithGraphData($event_id, $user_id = null): Event|null
    {
        $qb = em()->createQueryBuilder();
        $qb->select('e', 'ee', 'r', 're', 'c')
            ->from(Event::class, 'e')
            ->leftJoin('e.entries', 'ee', Join::WITH, 'ee.user = :uid')
            ->leftJoin('e.races', 'r')
            ->leftJoin('r.entries', 're', Join::WITH, 're.user = :uid')
            ->leftJoin('re.category', 'c')
            ->where('e.id = :eid')
            ->setParameters(['eid' => $event_id, 'uid' => $user_id]);
        try {
            return $qb->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            force_404("this event does not exist");
            return null;
        }
    }

    /** @return EventEntry[] */
    static function getAllEntries($event_id): array
    {
        return em()->createQueryBuilder()
            ->select('e')
            ->from(EventEntry::class, 'e')
            ->where('e.event = :eid')
            ->setParameters(['eid' => $event_id])
            ->getQuery()->getResult();
    }

    /** @return EventDto[] */
    static function listAllOpen($user_id)
    {
        $events = em()
            ->createQuery("SELECT ev.id, ev.name, ev.start_date, ev.end_date, ev.deadline, ev.open, en.present FROM Event ev" .
                " LEFT JOIN ev.entries en WITH en.user = ?1" .
                " WHERE ev.open = 1" .
                " ORDER BY ev.start_date DESC")
            ->setParameter(1, $user_id)
            ->getArrayResult();

        return EventDto::fromEventList($events);
    }

    static function getById($event_id, $user_id = 0)
    {
        return em()
            ->createQuery("SELECT ev, en FROM Event ev LEFT JOIN ev.entries en LEFT JOIN en.user u" .
                " WHERE ev.id = :eid AND (u.id IS NULL OR u.id = :uid)")
            ->setParameters(['eid' => $event_id, 'uid' => $user_id])
            ->getSingleResult();
    }

    static function listDrafts()
    {
        $events = em()
            ->createQuery("SELECT ev.id, ev.name, ev.start_date, ev.end_date, ev.deadline, ev.open FROM Event ev" .
                " WHERE ev.open = 0" .
                " ORDER BY ev.start_date DESC")
            ->getArrayResult();
        return EventDto::fromEventList($events);
    }
}

class EventDto
{
    public int $id;
    public string $name;
    public DateTime $start;
    public DateTime $end;
    public DateTime $deadline;
    public bool $open;
    public bool|null $registered;

    function __construct(
        $id,
        $name,
        $start,
        $end,
        $deadline,
        $open,
        $registered,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->deadline = $deadline;
        $this->open = $open;
        $this->registered = $registered;
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