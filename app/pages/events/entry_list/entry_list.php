<?php

$event_id = get_route_param("event_id");
$event_infos = EventService::getEventInfos($event_id);
page($event_infos->name . " : Inscrits")->css("entry_list.css") ?>

<nav id="page-actions">
    <a href="/evenements/<?= $event_id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <?php if ($event_infos->open): ?>
        <button onclick="selectTable()">Copier le tableau</button>
    <?php endif ?>
</nav>

<?php if (!$event_infos->open): ?>
    <p class="center">
        <?php "L'évenement de cette course n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <div id="tabs" hx-target="#tabs" hx-swap="innerHTML">
        <?= Component::render(__DIR__ . "/entry_list_tabs.php", ["event_id" => $event_id]) ?>
    </div>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>

<?= UserModal::renderRoot() ?>