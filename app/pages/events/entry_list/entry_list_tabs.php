<?php
include_once __DIR__ . "/TotalRow.php";

$eventId = Component::prop("event_id") ?? get_route_param("event_id") ?? throw new Exception("no event selected");
$selectedActivityId = Component::prop("activity_id") ?? get_query_param("activity_id", false) ?? null;
$activities = EventService::getActivityIdList($eventId);
$getProps = fn($isSelected) =>
    'role="tab" aria-controls="tab-content" '
    . ($isSelected ? 'aria-selected="true" class="contrast" autofocus' : 'class="secondary outline" aria-selected="false"');
?>

<div class="tab-list" role="tablist">
    <button hx-get="<?= "/evenements/$eventId/participants/tabs" ?>" <?= $getProps(!$selectedActivityId) ?>>Déplacement</button>
    <?php foreach ($activities as $activity): ?>
        <button id="<?= "activity$activity->id" ?>" hx-swap="innerHTML show:#<?= "activity$activity->id" ?>:top"
            hx-get="<?= "/evenements/$eventId/participants/tabs?activity_id=$activity->id" ?>"
            <?= $getProps($selectedActivityId == $activity->id) ?>>
            <?= $activity->name ?>
        </button>
    <?php endforeach ?>
</div>

<?= $selectedActivityId ? Component::render(__DIR__ . "/entry_list_tab_activity.php", ["activity_id" => $selectedActivityId])
    : Component::render(__DIR__ . "/entry_list_tab_event.php", ["event_id" => $eventId]) ?>