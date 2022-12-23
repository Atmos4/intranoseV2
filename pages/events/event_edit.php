<?php
restrict_access("ROOT", "STAFF", "COACH", "COACHSTAFF");

require_once "database/events.api.php";

$event_id = get_route_param("event_id", false);
$post = $_POST;
if ($event_id) {
    $event = get_event_by_id($event_id, $_SESSION['user_id']);
    if (empty($_POST)) {
        $post = [
            "event_name" => $event["nom"],
            "start_date" => $event["depart"],
            "end_date" => $event["arrivee"],
            "limit_date" => $event["limite"]
        ];
    }
}

$v = validate($post);
$event_name = $v->string("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date("start_date")->label("Date de départ")->required();
$end_date = $v->date("end_date")->label("Date de retour")->required()->after($start_date->value);
$limit_date = $v->date("limit_date")->label("Deadline")->required()->before($start_date->value);

if (!empty($_POST) && $v->valid()) {
    create_or_edit_event($event_name->value, $start_date->value, $end_date->value, $limit_date->value, $event_id);
}

page($event_id ? "{$event["nom"]} : Modifier" : "Créer un événement");
?>
<form method="post">
    <div class="page-actions">
        <a href="/evenements<?= $event_id ? "/$event_id" : "" ?>" class="secondary">
            <i class="fas fa-xmark"></i> Annuler
        </a>
        <?php if ($event_id):
            if (!$event['open']): ?>
        <a href="/evenements/<?= $event_id ?>/supprimer" class="destructive">
            <i class="fas fa-trash"></i> Supprimer
        </a>
        <?php elseif ($event['open']): ?>
        <a href="/evenements/<?= $event_id ?>/publier" class="destructive">
            <i class="fas fa-calendar-minus"></i> Retirer
        </a>
        <?php endif; endif; ?>
    </div>
    <article class="row">
        <?= $v->render_errors() ?>
        <?= $event_name->render() ?>
        <div class="col-sm-6 col-lg-4"><?= $start_date->render() ?></div>
        <div class="col-sm-6 col-lg-4"><?= $end_date->render() ?></div>
        <div class="col-lg-4"><?= $limit_date->render() ?></div>
        <div>
            <button type="submit"><?= $event_id ? "Modifier" : "Créer" ?></button>
        </div>
    </article>
</form>