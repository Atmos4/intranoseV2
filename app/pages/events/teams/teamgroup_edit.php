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
        Toast::error("Groupe d'équipes introuvable");
        redirect("/evenements/$event_id?tab=pools");
    }
}

$is_simple = $event->type == EventType::Simple;
$team_count = $is_new ? 0 : count($team_group->teams);

$activities = EventService::getActivityIdList($event_id);
$activity_options = ["" => "— Aucune —"];
foreach ($activities as $a) {
    $activity_options[$a->id] = $a->name;
}

$form_values = [
    "name" => $team_group->name,
    "activity" => $is_simple ? $activities[0]->id : $team_group->activity?->id,
    "relay_format" => $team_group->relay_format,
];

$v = new Validator($form_values);
$name = $v->text("name")->required()->placeholder("Nom")->label("Nom du groupe d'équipes");
$activity = $v->select("activity")->options($activity_options)->label("Activité liée");
$relay_format = $v->select("relay_format")->options(RelayFormatService::groupOptions())->label("Type de compétition");

if ($v->valid()) {
    $team_group->name = $name->value;
    if ($is_simple) {
        $team_group->activity = em()->find(Activity::class, $activities[0]->id);
    } else {
        $team_group->activity = $activity->value ? em()->find(Activity::class, $activity->value) : null;
    }
    if ($relay_format->value != $team_group->relay_format) {
        foreach ($team_group->teams as $existing_team) {
            em()->remove($existing_team);
        }
        $team_group->teams->clear();
        $team_group->relay_format = $relay_format->value ?: null;
    }
    em()->persist($team_group);
    em()->flush();

    if ($is_new) {
        Toast::success("Groupe d'équipes créé");
        redirect("/evenements/$event_id/pool/$team_group->id");
    } else {
        Toast::success("Groupe d'équipes modifié");
        redirect("/evenements/$event_id/pool/$pool_id");
    }
}

page($is_new ? "Nouveau Groupe d'Équipes" : "Modifier " . ($team_group->name ?: "Groupe #$pool_id"));
?>

<?= actions()->back($is_new ? "/evenements/$event_id?tab=pools" : "/evenements/$event_id/pool/$pool_id") ?>

<div class="container">
    <article>
        <header>
            <h2><?= $is_new ? "Créer un nouveau Groupe d'Équipes" : "Modifier le Groupe d'Équipes" ?></h2>
        </header>
        <form method="post" id="teamgroup-form">
            <?= $v->render_validation() ?>
            <?= $name->render() ?>
            <?php if (!$is_simple): ?>
                <?= $activity->render() ?>
            <?php endif ?>
            <?= $relay_format->render() ?>
            <?php if ($team_count > 0): ?>
                <p><i class="fa fa-triangle-exclamation"></i> Changer le type de compétition supprimera les <?= $team_count ?> équipe<?= $team_count > 1 ? 's' : '' ?> déjà composée<?= $team_count > 1 ? 's' : '' ?> dans ce groupe.</p>
            <?php endif ?>

            <button type="submit">
                <i class="fa <?= $is_new ? 'fa-plus' : 'fa-save' ?>"></i>
                <?= $is_new ? "Créer le Groupe" : "Enregistrer" ?>
            </button>
        </form>
    </article>
</div>

<?php if ($team_count > 0): ?>
    <script>
        (function () {
            const form = document.getElementById("teamgroup-form");
            const select = form.querySelector('[name="relay_format"]');
            const originalValue = select.value;
            document.body.addEventListener("htmx:confirm", function (evt) {
                if (evt.target !== form || select.value === originalValue) return;
                evt.preventDefault();
                if (confirm("Changer le type de compétition va supprimer les <?= $team_count ?> équipe<?= $team_count > 1 ? 's' : '' ?> déjà composée<?= $team_count > 1 ? 's' : '' ?> de ce groupe. Continuer ?")) {
                    evt.detail.issueRequest(true);
                }
            });
        })();
    </script>
<?php endif ?>