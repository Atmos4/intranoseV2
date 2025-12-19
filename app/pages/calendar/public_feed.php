<?php

/**
 * Public Calendar Feed
 * Generates an iCal (.ics) feed for all published events
 * URL: /cal/club.ics
 * 
 * This feed can be subscribed to in any calendar application:
 * - Google Calendar
 * - Apple Calendar
 * - Outlook
 * - Any CalDAV client
 */

try {
    $icalContent = CalendarFeedService::generatePublicFeed();
    $club = ClubManagementService::create()->getClub();
    $filename = ($club->slug ?? 'club') . '-calendar.ics';

    CalendarFeedService::outputFeed($icalContent, $filename);
} catch (Exception $e) {
    logger()->error("Error generating public calendar feed: " . $e->getMessage());
    http_response_code(500);
    echo "Error generating calendar feed. Please try again later.";
    exit;
}
