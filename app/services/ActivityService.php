<?php
class ActivityService
{
    /** @return ActivityEntry[] */
    static function getActivityEntries($activityId)
    {
        return em()->createQueryBuilder()
            ->select("a")
            ->from(ActivityEntry::class, "a")
            ->leftJoin("a.user", "u")
            ->where("a.activity = :activityId")
            ->setParameters(['activityId' => $activityId])
            ->getQuery()
            ->getResult();
    }
}