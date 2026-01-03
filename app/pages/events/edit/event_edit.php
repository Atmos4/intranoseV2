<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);

$event = $event_id ? em()->find(Event::class, $event_id) : null;

$is_simple = $event?->type == EventType::Simple;

page($event_id ? "{$event->name} : Modifier" : "Créer un événement")->enableHelp();
?>
<div class="row center" data-intro="Vous pouvez créer deux types d'événement : <b>simple</b> ou <b>complexe</b>.">
    <div>Combien d'activités ?</div>
    <label>
        <b
            data-intro="Un événement complexe correspond à un événement sur plusieurs jours ou avec plusieurs activités.">Plusieurs</b>
        <input name="type" type="checkbox" role="switch"
            hx-get="/evenements<?= $event_id ? "/$event_id" : "" ?>/event_form" hx-target="#form-div"
            hx-swap="innerHTML" hx-trigger="change" value="simple" <?= $is_simple ? "checked" : "" ?>
            data-intro="Une fois créé, vous pouvez passer d'un événement simple à complexe mais pas l'inverse"
            data-step="4" />
        <b data-intro="Un événement simple est un événement avec une activité.">Une seule</b>
    </label>
</div>
<div id="form-div"
    hx-get="/evenements<?= $event_id ? "/$event_id" : "" ?>/event_form<?= $is_simple ? "?type=simple" : "" ?>"
    hx-trigger="load">
</div>