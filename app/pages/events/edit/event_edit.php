<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);

$event = $event_id ? em()->find(Event::class, $event_id) : null;

$is_simple = $event?->type == EventType::Simple;

page($event_id ? "{$event->name} : Modifier" : "Créer un événement");
?>
<div class="row center">
    <label>
        <b>Complexe</b>
        <input name="type" type="checkbox" role="switch" hx-get="/evenements<?= $event_id ? "/$event_id" : "" ?>/event_form"
            hx-target="#form-div" hx-swap="innerHTML" hx-trigger="change" value="simple" <?= $is_simple ? "checked" : "" ?> />
        <b>Simple</b>
    </label>
</div>
<div id="form-div" hx-get="/evenements<?= $event_id ? "/$event_id" : "" ?>/event_form<?= $is_simple ? "?type=simple" : "" ?>"
    hx-trigger="load">
</div>