<?php
include_once __DIR__ . "/TotalRow.php";

$eventId = Component::prop("event_id") ?? get_route_param("event_id") ?? throw new Exception("no event selected");
$selectedRaceId = Component::prop("race_id") ?? get_query_param("race_id", false) ?? null;
$races = EventService::getRaceIdList($eventId);
$getProps = fn($isSelected) =>
    'role="tab" aria-controls="tab-content" '
    . ($isSelected ? 'aria-selected="true" class="contrast" autofocus' : 'class="secondary outline" aria-selected="false"');
?>

<div class="tab-list" role="tablist">
    <button hx-get="<?= "/evenements/$eventId/participants/tabs" ?>" <?= $getProps(!$selectedRaceId) ?>>Déplacement</button>
    <?php foreach ($races as $race): ?>
        <button id="<?= "race$race->id" ?>" hx-swap="innerHTML show:#<?= "race$race->id" ?>:top"
            hx-get="<?= "/evenements/$eventId/participants/tabs?race_id=$race->id" ?>"
            <?= $getProps($selectedRaceId == $race->id) ?>>
            <?= $race->name ?>
        </button>
    <?php endforeach ?>
</div>

<?= $selectedRaceId ? Component::render(__DIR__ . "/entry_list_tab_race.php", ["race_id" => $selectedRaceId])
    : Component::render(__DIR__ . "/entry_list_tab_event.php", ["event_id" => $eventId]) ?>