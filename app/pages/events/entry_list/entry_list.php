<?php

$event_id = get_route_param("event_id");
$event_infos = EventService::getEventInfos($event_id);
$is_simple = get_query_param("is_simple", false, false);
?>

<?php if ($event_infos->open && check_auth(Access::$ADD_EVENTS)): ?>
    <div class="entries-header"><button onclick="selectTable()"> Copier le tableau</button></div>
<?php endif ?>
<?php if (!$event_infos->open): ?>
    <p class="center">
        <?php "L'évenement n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <div id="tabs" hx-target="#tabs" hx-swap="innerHTML">
        <?= $is_simple ? component(__DIR__ . "/entry_list_tab_activity.php")->render(["activity_id" => EventService::getActivityIdList($event_id)[0]->id]) : component(__DIR__ . "/entry_list_tabs.php")->render(["event_id" => $event_id]) ?>
    </div>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>