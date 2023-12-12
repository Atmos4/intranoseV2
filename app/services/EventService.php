<?php
class EventService
{
    /** @return RaceInfoDto[] */
    static function getRaceIdList(string $event_id)
    {
        return em()->createQueryBuilder()
            ->select("NEW RaceInfoDto(r.id, r.name)")
            ->from(Race::class, "r")
            ->where("r.event = :eid")
            ->setParameter("eid", $event_id)
            ->getQuery()->getResult();
    }

    static function getEventInfos(string $event_id): EventInfoDto
    {
        return em()->createQueryBuilder()
            ->select("NEW EventInfoDto(e.id, e.name, e.open)")
            ->from(Event::class, "e")
            ->where("e = :eid")
            ->setParameter("eid", $event_id)
            ->getQuery()->getSingleResult();
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