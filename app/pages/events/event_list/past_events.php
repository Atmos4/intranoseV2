<?php
include_once __DIR__ . "/RenderEvents.php";

restrict_access();
formatter("d MMM");
$user = User::getCurrent();
$past_events = Event::listAllPastOpen($user->id);

if (count($past_events)) {
    foreach ($past_events as $event) {
        render_events_article($event);
    }
}