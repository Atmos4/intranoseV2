<?php

use Google\Client;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleCalendarEvent;
class GoogleCalendarService
{
    private $client;
    private $service;
    public $calendarId;

    public function __construct()
    {
        $this->client = new Client();
        $club = ClubManagementService::create()->getClub();
        if (!$club->google_credential_path || !$club->google_calendar_id) {
            throw new Exception('Google Calendar credentials are not set for this club.');
        }

        $this->client->setAuthConfig($club->google_credential_path);
        if (!$this->checkCredentials()) {
            throw new Exception('Google Calendar credentials are invalid.');
        }

        $this->client->setScopes('https://www.googleapis.com/auth/calendar');
        $this->client->setApplicationName("My Calendar");

        $this->calendarId = $club->google_calendar_id;

        $this->service = new GoogleCalendar($this->client);

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
            'description' => ($event->description ? $event->description . '<br><br>' : '') . "<b>Deadline d'inscription</b> : " . $event->deadline->format('d/m/Y') . "<br><br> <a href='" . env('BASE_URL') . '/evenements/' . $event->id . "'>Lien vers l'événement </a>",
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
            $google_calendar = new GoogleCalendarService();
            $google_calendar->deleteEvent($event->google_calendar_id);
            $calendar_event = $google_calendar->createEvent($event);
            $event->google_calendar_id = $calendar_event->getId();
            $event->google_calendar_url = $calendar_event->getHtmlLink();
        }
    }

    static function clearCredentialFolder()
    {
        try {
            $files = glob(Path::credentials() . '/*'); // get all file names
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); // delete file
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}