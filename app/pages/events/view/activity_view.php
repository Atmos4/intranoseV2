<?php
restrict_access();

include __DIR__ . "/../eventUtils.php";

$event_id = get_route_param("event_id", false);
$activity = em()->find(Activity::class, get_route_param("activity_id"));

$activity_entry = $activity->entries[0] ?? null;
$can_edit = check_auth(Access::$ADD_EVENTS);

page($activity->name)->css("event_view.css");
?>

<?= actions()->back("/evenements/$event_id")
    ->if(
        $can_edit,
        // I love functional programming :D
        fn($a) => $a->dropdown(fn($b) => $b
            ->link("/evenements/$event_id/activite/$activity->id/modifier", "Éditer", "fas fa-pen")
            ->link("/evenements/$event_id/activite/$activity->id/supprimer", "Supprimer", "fas fa-trash", ["class" => "destructive"]))
    ) ?>

<?= RenderActivityEntry($activity) ?>

<?php require_once app_path() . "/pages/events/view/ActivityView.php";