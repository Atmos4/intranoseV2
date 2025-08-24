<?php

$event_id = get_route_param("event_id");
$event_infos = EventService::getEventInfos($event_id);
$is_simple = get_query_param("is_simple", false, false);
// Get all user emails who are present at the event
$user_emails = em()->createQuery('
    SELECT DISTINCT u.real_email 
    FROM EventEntry ee 
    JOIN ee.user u 
    WHERE ee.event = :eid AND ee.present = :pres AND u.real_email IS NOT NULL
')
    ->setParameters(["eid" => $event_id, "pres" => true])
    ->getSingleColumnResult();

// Get all family leader emails for users who are present but not family leaders themselves
$family_leader_emails = em()->createQuery('
    SELECT DISTINCT fl.real_email 
    FROM EventEntry ee 
    JOIN ee.user u 
    JOIN User fl WITH fl.family = u.family 
    WHERE ee.event = :eid AND ee.present = :pres 
    AND u.family_leader = false
    AND fl.family_leader = true 
    AND fl.real_email IS NOT NULL
')
    ->setParameters(["eid" => $event_id, "pres" => true])
    ->getSingleColumnResult();

// Combine and deduplicate all emails
$event_email_list = array_unique(array_merge($user_emails, $family_leader_emails));
$string_email_list = implode(',', $event_email_list);
$dropdown = new PageActionDropdownBuilder(label: "Actions", rtl: true, attributes: ["data-intro" => "Vous pouvez copier le contenu du tableau affiché ici"], standalone: true);
$dropdown->link("#", "Copier le tableau", icon: "fa-clipboard", attributes: ["onclick" => "selectTable()", "role" => "link"])
    ->link("#", "Copier la liste d'emails de l'événement", icon: "fa-clipboard", attributes: ["onclick" => "copyEntryEmails(\"$string_email_list\")", "role" => "link"])
    ?>

<div class="entries-header"><?= $dropdown ?></div>
<?php if (!$event_infos->open): ?>
    <p class="center">
        <?= "L'évenement n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <div id="tabs" hx-target="#tabs" hx-swap="innerHTML">
        <?= $is_simple ? component(__DIR__ . "/entry_list_tab_activity.php")->render(["activity_id" => EventService::getActivityIdList($event_id)[0]->id]) : component(__DIR__ . "/entry_list_tabs.php")->render(["event_id" => $event_id]) ?>
    </div>
<?php endif ?>