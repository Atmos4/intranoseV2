<?php
include_once __DIR__ . "/RenderEvents.php";
$date = $_GET['date'] ?? date('Y-m-d');
$formatted_date = new DateTime($date);
$events = EventService::getEventsForDay($date, User::getCurrentUserId());

if (!$events) { ?>
    <p style="text-align:center">Rien le <?= $formatted_date->format("d/m") ?> </p>
<?php }

foreach ($events as $e) {
    render_events($e);
}