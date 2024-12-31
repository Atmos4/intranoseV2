<?php
check_auth();
restrict(dev_or_staging());

$google_calendar = new GoogleCalendarService();

$timeMin = date('c', strtotime('-1 month'));
$timeMax = date('c');

$events = $google_calendar->getEvents($timeMin, $timeMax);

$v = new Validator(action: "create_form");
$summary = $v->text("summary")->placeholder("Event Summary")->required();
$start_date = $v->date("start")->label("Start Date")->required();
$end_date = $v->date("end")->label("End Date")->required();

if ($v->valid()) {
    $new_event = new Event();
    $new_event->name = $summary->value;
    $new_event->start_date = date_create($start_date->value);
    $new_event->end_date = date_create($end_date->value);
    $new_event->deadline = date_create($start_date->value);
    $event = $google_calendar->createEvent($new_event);

    $v->set_success('Event created: ' . $event->htmlLink);
}

$v_delete = new Validator(action: "delete_form");
$delete_id = $v_delete->text("delete_id")->placeholder("Event Id")->required();

if ($v_delete->valid()) {
    $success = $google_calendar->deleteEvent($delete_id->value);

    if ($success) {
        $v_delete->set_success('Event deleted');
    } else {
        $v_delete->set_error('Event not found');
    }
}
page("Google Calendar");
?>
<h4>Evénements sur le dernier mois pour le calendrier <b><?= $google_calendar->calendarId ?></b></h4>
<ul>
    <?php
    foreach ($events->getItems() as $event) {
        $start = $event->start->dateTime ?: $event->start->date; ?>
        <li><?= $event->getId() . " (" . $start . ")"; ?></li>
    <?php } ?>
</ul>
<h2>Créer un nouvel événement</h2>
<form method="post">
    <?= $v->render_validation() ?>
    <?= $summary->render() ?>
    <?= $start_date->render() ?>
    <?= $end_date->render() ?>
    <input type="submit" value="Create Event">
</form>
<h2>Supprimer un événement</h2>
<form method="post">
    <?= $v_delete->render_validation() ?>
    <?= $delete_id->render() ?>
    <input type="submit" value="Delete Event">
</form>