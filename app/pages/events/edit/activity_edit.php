<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);
$activity_id = get_route_param("activity_id", false);
$event = em()->find(Event::class, $event_id);

if ($activity_id) {
    $activity = em()->find(Activity::class, $activity_id);
    if (!$activity) {
        redirect("/evenements/$event_id/activite/nouveau");
    }
    if ($activity->event && $activity->event->id != $event_id) {
        redirect("/evenements/{$activity->event->id}/activite/$activity_id/modifier");
    }
}

if ($event->type == EventType::Simple) {
    return "Pas d'ajout d'activité possible pour un événement simple";
}

page($activity_id ? "{$activity->name} : Modifier" : "Ajouter une activité")->css("activity_edit.css");
?>
<form method="post">
    <?= actions()->back("/evenements/$event_id", "Annuler", "fas fa-xmark")->submit($activity_id ? "Modifier" : "Créer") ?>
    <?php
    require_once app_path() . "/pages/events/edit/ActivityEditForm.php";
    ?>
</form>