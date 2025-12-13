<?php
// Kind of an experiment, refine if needed
class Path
{
    const LOGS = BASE_PATH . "/.logs";
    static function uploads(string ...$parts)
    {
        return club_data_path(ClubManagementService::getSelectedClubSlug(), "uploads", ...$parts);
    }

    static function profilePicture($slug = null)
    {
        return relative_club_data_path($slug ?? ClubManagementService::getSelectedClubSlug(), "profile");
    }

    static function credentials()
    {
        return club_data_path(ClubManagementService::getSelectedClubSlug(), "credentials");
    }
}