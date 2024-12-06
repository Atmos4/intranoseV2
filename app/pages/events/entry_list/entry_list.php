<?php

$event_id = get_route_param("event_id");
$event_infos = EventService::getEventInfos($event_id);
$is_simple = get_query_param("is_simple", false, false);
$event_email_list = em()->createQuery('SELECT u.real_email FROM EventEntry ee JOIN ee.user u WHERE ee.event = :eid')
    ->setParameter("eid", $event_id)
    ->getSingleColumnResult();
$string_email_list = implode(',', $event_email_list); ?>

<?= actions()
    ->if(
        $event_infos->open && check_auth(Access::$ADD_EVENTS),
        fn($a) => $a->dropdown(function ($dropdown) use ($string_email_list) {
            $dropdown->button("Copier le tableau", icon: "fa-clipboard", attributes: ["onclick" => "selectTable()", "role" => "link"]);
            $dropdown->button("Copier la liste d'emails de l'événement", icon: "fa-clipboard", attributes: ["onclick" => "copyEntryEmails(\"$string_email_list\")", "role" => "link"]);
        }, ["data-intro" => "Vous pouvez copier le contenu du tableau affiché ici"])
    ) ?>
?>
<?php if (!$event_infos->open): ?>
    <p class="center">
        <?= "L'évenement n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <div id="tabs" hx-target="#tabs" hx-swap="innerHTML">
        <?= $is_simple ? component(__DIR__ . "/entry_list_tab_activity.php")->render(["activity_id" => EventService::getActivityIdList($event_id)[0]->id]) : component(__DIR__ . "/entry_list_tabs.php")->render(["event_id" => $event_id]) ?>
    </div>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>