<?php

$event_id = get_route_param("event_id");
$event_infos = EventService::getEventInfos($event_id);
page($event_infos->name . " : Inscrits")->css("entry_list.css") ?>

<?= actions()
    ->back("/evenements$event_id")
    ->if(
        $event_infos->open && check_auth(Access::$ADD_EVENTS),
        fn($a) => $a->button("Copier le tableau", attributes: ["onclick" => "selectTable()"])
    ) ?>

<?php if (!$event_infos->open): ?>
    <p class="center">
        <?php "L'évenement de cette course n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <div id="tabs" hx-target="#tabs" hx-swap="innerHTML">
        <?= component(__DIR__ . "/entry_list_tabs.php")->render(["event_id" => $event_id]) ?>
    </div>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>

<?= UserModal::renderRoot() ?>