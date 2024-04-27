<?php
include_once __DIR__ . "/RenderEvents.php";

restrict_access();
formatter("d MMM");
$user = User::getCurrent();
$past_events = EventService::listAllPastOpen($user->id);

?>
<h4>Évenements passés</h4>
<?php
if (count($past_events)) {
    foreach ($past_events as $event) {
        render_events_article($event);
    }
} else { ?>
    <p>Pas d'événements passés 😿</p>
<?php }