<?php
// Kind of an experiment, refine if needed
class Path
{
    const LOGS = BASE_PATH . "/.logs";
    static function uploads(string ...$parts)
    {
        return club_data_path(ClubManagementService::getSelectedClub(), "uploads", ...$parts);
    }

    static function club(...$parts)
    {
        return $parts;
    }

    static function profilePicture()
    {
        return path(".club_data", ".shared", "profile");
    }
}