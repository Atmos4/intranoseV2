<?php
class RaceService
{
    /** @return RaceEntry[] */
    static function getRaceEntries($raceId)
    {
        return em()->createQueryBuilder()
            ->select("r")
            ->from(RaceEntry::class, "r")
            ->leftJoin("r.user", "u")
            ->where("r.race = :raceId")
            ->setParameters(['raceId' => $raceId])
            ->getQuery()
            ->getResult();
    }
}