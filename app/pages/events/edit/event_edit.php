<?php
restrict_access(Access::$ADD_EVENTS);

$event_creation_type = get_query_param("type", false, false);
$event_id = get_route_param("event_id", strict: false);

$event = $event_id ? em()->find(Event::class, $event_id) : null;

$display_type = match (true) {
    $event_creation_type == "simple" => "simple",
    $event_creation_type == "complex" => "complexe",
    default => ""
};

page($event_id ? "{$event->name} : Modifier" : "Créer un événement " . $display_type);
?>
<form method="post">
    <?= actions()?->back("/evenements" . ($event_id ? "/$event_id" : ""), "Annuler")->submit($event_id ? "Modifier" : "Créer") ?>
    <?php
    if ($event_creation_type === "simple" || $event?->type == EventType::Simple) {
        require_once app_path() . "/pages/events/edit/ActivityEditForm.php";
    } else {
        require_once app_path() . "/pages/events/edit/EventEditForm.php";
    } ?>
</form>