<?php

use Google\Client;
use Google\Service\Calendar as GoogleCalendarService;
use Google\Service\Calendar\Event as GoogleCalendarEvent;
class GoogleCalendar
{
    private $client;
    private $service;
    public $calendarId;

    public function __construct()
    {
        $this->client = new Client();
        if (!env('GOOGLE_CREDENTIAL_PATH') || !env('GOOGLE_CALENDAR_ID')) {
            throw new Exception('Google Calendar credentials are not set in the environment variables.');
        }

        $this->client->setAuthConfig(env('GOOGLE_CREDENTIAL_PATH'));
        if (!$this->checkCredentials()) {
            throw new Exception('Google Calendar credentials are invalid.');
        }

        $this->client->setScopes('https://www.googleapis.com/auth/calendar');
        $this->client->setApplicationName("My Calendar");

        $this->calendarId = env("GOOGLE_CALENDAR_ID");

        $this->service = new GoogleCalendarService($this->client);

        $calendar = $this->service->calendars->get($this->calendarId);
        if (!$calendar) {
            throw new Exception('Google Calendar does not exist.');
        }

    }
    public function checkCredentials()
    {
        try {
            $this->client->fetchAccessTokenWithAssertion();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function createEvent($event)
    {
        // Adding one day to the end date to make it inclusive
        // TODO : parse description markdown to html
        $calendar_event = new GoogleCalendarEvent([
            'summary' => $event->name,
            'start' => [
                'date' => date('Y-m-d', strtotime($event->start_date->format('Y-m-d'))),
                'timeZone' => 'Europe/Paris',
            ],
            'end' => [
                'date' => date('Y-m-d', strtotime($event->end_date->format('Y-m-d') . ' +1 day')),
                'timeZone' => 'Europe/Paris',
            ],
            'transparency' => 'transparent',
            'description' => ($event->description ? $event->description . '<br><br>' : '') . "<b>Deadline d'inscription</b> : " . $event->end_date->format('d/m/Y') . "<br><br> <a href='" . env('BASE_URL') . '/evenements/' . $event->id . "'>Lien vers l'événement </a>",
        ]);

        $result = $this->insertEvent($calendar_event);
        return $result;
    }

    public function insertEvent($event)
    {
        return $this->service->events->insert($this->calendarId, $event);
    }

    public function deleteEvent($event_id)
    {
        try {
            $this->service->events->get($this->calendarId, $event_id);
            $this->service->events->delete($this->calendarId, $event_id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getEvents($timeMin, $timeMax)
    {
        return $this->service->events->listEvents($this->calendarId, [
            'timeMin' => $timeMin,
            'timeMax' => $timeMax,
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ]);
    }

    static function updateEvent($event)
    {
        if ($event->google_calendar_id) {
            $google_calendar = new GoogleCalendar();
            $google_calendar->deleteEvent($event->google_calendar_id);
            $calendar_event = $google_calendar->createEvent($event);
            $event->google_calendar_id = $calendar_event->getId();
            $event->google_calendar_url = $calendar_event->getHtmlLink();
        }
    }
}