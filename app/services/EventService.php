<?php

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

class EventService
{
    /** @return ActivityInfoDto[] */
    static function getActivityIdList(string $eventId)
    {
        return em()->createQueryBuilder()
            ->select("NEW ActivityInfoDto(r.id, r.name)")
            ->from(Activity::class, "r")
            ->where("r.event = :eid")
            ->setParameter("eid", $eventId)
            ->getQuery()->getResult();
    }

    static function getEventInfos(string $eventId): EventInfoDto
    {
        return em()->createQueryBuilder()
            ->select("NEW EventInfoDto(e.id, e.name, e.open)")
            ->from(Event::class, "e")
            ->where("e = :eid")
            ->setParameter("eid", $eventId)
            ->getQuery()->getSingleResult();
    }

    static function getEntryCount(string $eventId): int
    {
        return em()->createQueryBuilder()
            ->select("COUNT(e.present)")
            ->from(EventEntry::class, "e")
            ->where("e.event = :eid AND e.present=1")
            ->setParameter("eid", $eventId)
            ->getQuery()->getSingleScalarResult();
    }

    static function getEventWithAllData($event_id, $user_id = null): Event|null
    {
        $qb = em()->createQueryBuilder();
        $qb->select('e', 'ee', 'r', 're', 'c')
            ->from(Event::class, 'e')
            ->leftJoin('e.entries', 'ee', Join::WITH, 'ee.user = :uid')
            ->leftJoin('e.activities', 'r')
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

    /** @return EventDto[] */
    static function listAllFutureOpenEvents($user_id)
    {
        $events = em()
            ->createQuery("SELECT ev.id, ev.name, ev.start_date, ev.end_date, ev.deadline, ev.open, en.present FROM Event ev" .
                " LEFT JOIN ev.entries en WITH en.user = ?1" .
                " WHERE ev.open = 1" .
                " AND ev.end_date > CURRENT_DATE()" .
                " ORDER BY ev.start_date DESC")
            ->setParameter(1, $user_id)
            ->getArrayResult();

        return EventDto::fromEventList($events);
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

    /** @return EventDto[] */
    static function listAllPastOpen($user_id)
    {
        $events = em()
            ->createQuery("SELECT ev.id, ev.name, ev.start_date, ev.end_date, ev.deadline, ev.open, en.present FROM Event ev" .
                " LEFT JOIN ev.entries en WITH en.user = ?1" .
                " WHERE ev.open = 1" .
                " AND ev.end_date <= CURRENT_DATE()" .
                " ORDER BY ev.start_date DESC")
            ->setParameter(1, $user_id)
            ->getArrayResult();

        return EventDto::fromEventList($events);
    }

    /** @return EventEntry[] */
    static function getAllEntries($event_id): array
    {
        return em()->createQueryBuilder()
            ->select('e', 'u', 're')
            ->from(EventEntry::class, 'e')
            ->join('e.event', 'ev')
            ->leftJoin('e.user', 'u')
            ->leftJoin('u.activity_entries', 're', JOIN::WITH, 're.user = e.user and re.activity MEMBER OF ev.activities', 're.activity')
            ->where('e.event = :eid')
            ->setParameters(['eid' => $event_id])
            ->getQuery()->getResult();
    }
}

class EventInfoDto
{
    function __construct(public int $id, public string $name, public bool $open)
    {
    }
}

class ActivityInfoDto
{
    function __construct(public int $id, public string $name)
    {
    }
}