<?php
include_once __DIR__ . "/RenderEvents.php";
$date = $_GET['date'] ?? date('Y-m-d');
$events = EventService::getEventsForDay($date, User::getCurrentUserId());

if (!$events) { ?>
    <p style="text-align:center">Rien aujourd'hui</p>
<?php }

foreach ($events as $e) {
    render_events($e);
}