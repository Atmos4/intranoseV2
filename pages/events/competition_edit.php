<?php
restrict_access("ROOT", "STAFF", "COACH", "COACHSTAFF");

require_once "database/competitions.api.php";
require_once "utils/form_validation.php";

$event_id = get_route_param("event_id");
$competition_id = get_route_param("competition_id", false);
$post = $_POST;
if ($competition_id) {
    $competition = get_competition($competition_id);
    if (empty($_POST)) {
        $post = [
            "name" => $competition["nom"],
            "date" => $competition["date"],
            "location" => $competition["lieu"],
            "limit_date" => $competition["limite"]
        ];
    }
}

$v = validate($post);
$name = $v->string("event_name")->label("Nom de la course")->placeholder()->required();
$date = $v->date("date")->label("Date")->required();
$location = $v->string("location")->label("Lieu")->required();

if (!empty($_POST) && $v->valid()) {
    create_or_edit_competition($name->value, $date->value, $location->value, $event_id, $competition_id);
}

page($competition_id ? "{$competition["nom"]} : Modifier" : "Ajouter une course");
?>
<form method="post">
    <div class="page-actions">
        <a href="/evenements/<?= $event_id ?>" class="secondary">
            <i class="fas fa-xmark"></i> Annuler
        </a>
    </div>
    <article class="row">
        <?= $v->render_errors() ?>
        <?= $name->render() ?>
        <?= $date->render() ?>
        <?= $location->render() ?>
        <div>
            <button type="submit"><?= $competition_id ? "Modifier" : "Créer" ?></button>
        </div>
    </article>
</form>