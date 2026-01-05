<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);
$is_simple = get_query_param("type", false, false) == "simple";

$event = $event_id ? em()->find(Event::class, $event_id) : null;

$is_simple = $is_simple ?: $event?->type == EventType::Simple;

page($event_id ? "{$event->name} : Modifier" : "Créer un événement " . ($is_simple ? "mono-activité" : "multi-activité"))->enableHelp();
?>
<div id="form-div"
    hx-get="/evenements<?= $event_id ? "/$event_id" : "" ?>/event_form<?= $is_simple ? "?type=simple" : "" ?>"
    hx-trigger="load">
</div>