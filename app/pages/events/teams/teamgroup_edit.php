<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param('event_id');
$pool_id = get_route_param('pool_id', false);
$event = em()->find(Event::class, $event_id);

$is_new = !$pool_id;

if ($is_new) {
    $team_group = new TeamGroup();
    $team_group->event = $event;
} else {
    $team_group = em()->find(TeamGroup::class, $pool_id);
    if (!$team_group || $team_group->event->id !== $event->id) {
        Toast::error("Pool d'équipes introuvable");
        redirect("/evenements/$event_id?tab=pools");
    }
}

$is_simple = $event->type == EventType::Simple;

$activities = EventService::getActivityIdList($event_id);
$activity_options = ["" => "— Aucune —"];
foreach ($activities as $a) {
    $activity_options[$a->id] = $a->name;
}

$form_values = [
    "name" => $team_group->name,
    "activity" => $is_simple ? $activities[0]->id : $team_group->activity?->id,
];

$v = new Validator($form_values);
$name = $v->text("name")->required()->placeholder("Nom")->label("Nom du pool d'équipes");
$activity = $v->select("activity")->options($activity_options)->label("Activité liée");

if ($v->valid()) {
    $team_group->name = $name->value;
    if ($is_simple) {
        $team_group->activity = em()->find(Activity::class, $activities[0]->id);
    } else {
        $team_group->activity = $activity->value ? em()->find(Activity::class, $activity->value) : null;
    }
    em()->persist($team_group);
    em()->flush();

    if ($is_new) {
        Toast::success("Pool d'équipes créé");
        redirect("/evenements/$event_id/pool/$team_group->id");
    } else {
        Toast::success("Pool d'équipes modifié");
        redirect("/evenements/$event_id/pool/$pool_id");
    }
}

page($is_new ? "Nouveau Pool d'Équipes" : "Modifier " . ($team_group->name ?: "Pool #$pool_id"));
?>

<?= actions()->back($is_new ? "/evenements/$event_id?tab=pools" : "/evenements/$event_id/pool/$pool_id") ?>

<div class="container">
    <article>
        <header>
            <h2><?= $is_new ? "Créer un nouveau Pool d'Équipes" : "Modifier le Pool d'Équipes" ?></h2>
        </header>
        <form method="post">
            <?= $v->render_validation() ?>
            <?= $name->render() ?>
            <?php if (!$is_simple): ?>
                <?= $activity->render() ?>
            <?php endif ?>

            <button type="submit">
                <i class="fa <?= $is_new ? 'fa-plus' : 'fa-save' ?>"></i>
                <?= $is_new ? "Créer le Pool" : "Enregistrer" ?>
            </button>
        </form>
    </article>
</div>