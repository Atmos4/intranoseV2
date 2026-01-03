<?php

use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event as ICalEvent;
use Eluceo\iCal\Domain\ValueObject\DateTime as ICalDateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Domain\ValueObject\Timestamp;
use Eluceo\iCal\Domain\ValueObject\Alarm;
use Eluceo\iCal\Domain\ValueObject\Alarm\DisplayAction;
use Eluceo\iCal\Domain\ValueObject\Alarm\AbsoluteDateTimeTrigger;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use \Eluceo\iCal\Domain\ValueObject\Location;

class CalendarFeedService
{
    /**
     * Generate a public iCal feed for all published events
     * @return string The iCal formatted string
     */
    public static function generatePublicFeed(): string
    {
        $club = ClubManagementService::create()->getClub();
        $clubName = $club->name ?? config("name", "Intranose");

        // Create calendar
        $calendar = new Calendar();

        // Get all published events (future and recent past events - last 6 months)
        $sixMonthsAgo = (new DateTime())->modify('-6 months');
        $events = em()->createQuery(
            "SELECT e, a FROM Event e 
            LEFT JOIN e.activities a
            WHERE e.open = 1 
            AND e.end_date >= :sixMonthsAgo
            ORDER BY e.start_date"
        )
            ->setParameter('sixMonthsAgo', $sixMonthsAgo)
            ->getResult();

        foreach ($events as $event) {
            $calendar->addEvent(self::createICalEvent($event, $clubName));
        }

        // Convert to string
        $calendarFactory = new CalendarFactory();
        $calendarComponent = $calendarFactory->createCalendar($calendar);

        return $calendarComponent->__toString();
    }

    /**
     * Create an iCal event from an Event entity
     * @param Event $event
     * @param string $clubName
     * @return ICalEvent
     */
    private static function createICalEvent(Event $event, string $clubName): ICalEvent
    {
        $baseUrl = env("BASE_URL");
        $eventUrl = "$baseUrl/evenements/$event->id";

        // Create unique identifier
        $uid = new UniqueIdentifier("event-{$event->id}@" . parse_url($baseUrl, PHP_URL_HOST));

        // Create the iCal event
        $iCalEvent = new ICalEvent($uid);

        // Set summary (title)
        $iCalEvent->setSummary("[$clubName] $event->name");

        // Set description
        $description = self::formatDescription($event, $eventUrl);
        $iCalEvent->setDescription($description);

        // Set time span (event with hours)
        $occurrence = new TimeSpan(
            new ICalDateTime($event->start_date, true),
            new ICalDateTime(
                $event->end_date,
                true
            )
        );
        $iCalEvent->setOccurrence($occurrence);

        // Set URL
        $iCalEvent->setUrl(new Uri($eventUrl));

        // Set location if available (from first activity)
        if ($event->activities && count($event->activities) > 0) {
            $firstActivity = $event->activities[0];
            if ($firstActivity->location_label) {
                $iCalEvent->setLocation(
                    new Location($firstActivity->location_label)
                );
            }
        }

        // Add deadline as alarm (reminder)
        if ($event->deadline < $event->start_date) {
            $alarm = new Alarm(
                new DisplayAction("Deadline d'inscription : $event->name"),
                new AbsoluteDateTimeTrigger(new Timestamp($event->deadline))
            );
            $iCalEvent->addAlarm($alarm);
        }

        // Set last modified time
        $iCalEvent->touch(new Timestamp(new DateTime()));

        return $iCalEvent;
    }

    /**
     * Format event description for iCal
     * @param Event $event
     * @param string $eventUrl
     * @return string
     */
    private static function formatDescription(Event $event, string $eventUrl): string
    {
        $parts = [];

        // Add event description if present
        if ($event->description) {
            $parts[] = $event->description;
            $parts[] = "";
        }

        // Add deadline information
        $parts[] = "Deadline d'inscription : " . $event->deadline->format('d/m/Y');
        $parts[] = "";

        // Add activities if present
        if ($event->activities && count($event->activities) > 0) {
            $parts[] = "Activités :";
            foreach ($event->activities as $activity) {
                $activityInfo = "- {$activity->name}";
                if ($activity->date) {
                    $activityInfo .= " (" . $activity->date->format('d/m/Y') . ")";
                }
                $parts[] = $activityInfo;
            }
            $parts[] = "";
        }

        // Add link to event
        $parts[] = "Plus d'informations et inscription :";
        $parts[] = $eventUrl;

        return implode("\n", $parts);
    }

    /**
     * Output the iCal feed with appropriate headers
     * @param string $icalContent
     * @param string $filename
     */
    public static function outputFeed(string $icalContent, string $filename = 'calendar.ics'): void
    {
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: public, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

        echo $icalContent;
        exit;
    }
}
