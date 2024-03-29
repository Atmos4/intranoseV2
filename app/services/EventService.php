<?php
class EventService
{
    /** @return RaceInfoDto[] */
    static function getRaceIdList(string $eventId)
    {
        return em()->createQueryBuilder()
            ->select("NEW RaceInfoDto(r.id, r.name)")
            ->from(Race::class, "r")
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
}

class EventInfoDto
{
    function __construct(public int $id, public string $name, public bool $open)
    {
    }
}

class RaceInfoDto
{
    function __construct(public int $id, public string $name)
    {
    }
}