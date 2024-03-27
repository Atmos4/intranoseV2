<?php
class ActivityService
{
    /** @return ActivityEntry[] */
    static function getActivityEntries($activityId)
    {
        return em()->createQueryBuilder()
            ->select("r")
            ->from(ActivityEntry::class, "r")
            ->leftJoin("r.user", "u")
            ->where("r.activity = :activityId")
            ->setParameters(['activityId' => $activityId])
            ->getQuery()
            ->getResult();
    }
}