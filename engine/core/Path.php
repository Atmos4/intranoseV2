<?php

// Kind of an experiment, refine if needed
class Path
{
    public const LOGS = BASE_PATH . "/.logs";
    public static function uploads(string ...$parts)
    {
        return club_data_path(ClubManagementService::getSelectedClubSlug(), "uploads", ...$parts);
    }

    public static function profilePicture($slug = null)
    {
        return relative_club_data_path($slug ?? ClubManagementService::getSelectedClubSlug(), "profile");
    }

    public static function credentials()
    {
        return club_data_path(ClubManagementService::getSelectedClubSlug(), "credentials");
    }
}
