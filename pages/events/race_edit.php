<?php
restrict_access(
    Permission::COACH,
    Permission::STAFF,
    Permission::ROOT,
    Permission::COACHSTAFF
);

$event_id = get_route_param("event_id");
$race_id = get_route_param("race_id", false);
$event = em()->find(Event::class, $event_id);
if (!$event) {
    return "this event does not exist";
}
if ($race_id) {
    $race = em()->find(Race::class, $race_id);
    $form_values = [
        "name" => $race->name,
        "date" => date_format($race->date, "Y-m-d"),
        "place" => $race->place
    ];
} else {
    $race = new Race();
}

$v = new Validator($form_values ?? []);
$name = $v->text("name")->label("Nom de la course")->placeholder()->required();
$date = $v->date("date")->label("Date")->required();
$place = $v->text("place")->label("Lieu")->required();

if (!empty($_POST) && $v->valid()) {
    $race->set_values($name->value, date_create($date->value), $place->value, $event);
    em()->persist($race);
    em()->flush();
    redirect("/evenements/$event->id");
}

page($race_id ? "{$race->name} : Modifier" : "Ajouter une course");
?>
<form method="post">
    <div id="page-actions">
        <a href="/evenements/<?= $event_id ?>" class="secondary">
            <i class="fas fa-xmark"></i> Annuler
        </a>
    </div>
    <article class="row">
        <?= $v->render_validation() ?>
        <?= $name->render() ?>
        <div class="col-md-6">
            <?= $date->render() ?>
        </div>
        <div class="col-md-6">
            <?= $place->render() ?>
        </div>
        <div>
            <button type="submit">
                <?= $race_id ? "Modifier" : "Créer" ?>
            </button>
        </div>
    </article>
</form>