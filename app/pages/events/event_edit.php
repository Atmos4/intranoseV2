<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);

if ($event_id) {
    $event = em()->find(Event::class, $event_id);
    if ($event == null) {
        echo "Error: the event with id $event_id does not exist";
        return;
    }
    $event_mapping = [
        "event_name" => $event->name,
        "start_date" => date_format($event->start_date, "Y-m-d"),
        "end_date" => date_format($event->end_date, "Y-m-d"),
        "limit_date" => date_format($event->deadline, "Y-m-d"),
        "bulletin_url" => $event->bulletin_url,

    ];
} else {
    $event = new Event();
}

$v = new Validator($event_mapping ?? []);
$event_name = $v->text("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date("start_date")->label("Date de départ")->required();
$end_date = $v->date("end_date")
    ->label("Date de retour")->required()
    ->min($start_date->value, "Doit être après le départ", false);
$limit_date = $v->date("limit_date")
    ->label("Deadline")->required()
    ->max($start_date->value ? date_create($start_date->value)->sub(new DateInterval("PT23H59M59S"))->format("Y-m-d") : "", "Doit être avant le jour de départ", false);
$bulletin_url = $v->url("bulletin_url")->label("Lien vers le bulletin")->placeholder();

if (!empty($_POST) && $v->valid()) {
    $event->set($event_name->value, $start_date->value, $end_date->value, $limit_date->value, $bulletin_url->value ?? "");
    em()->persist($event);
    em()->flush();
    redirect("/evenements/$event->id");
}

page($event_id ? "{$event->name} : Modifier" : "Créer un événement");
?>
<form method="post">
    <nav id="page-actions">
        <a href="/evenements<?= $event_id ? "/$event_id" : "" ?>" class="secondary">
            <i class="fas fa-caret-left"></i> Annuler
        </a>
        <button type="submit">
            <?= $event_id ? "Modifier" : "Créer" ?>
        </button>
    </nav>
    <article>
        <div class="row">
            <?= $v->render_validation() ?>
            <?php if (isset($success)): ?>
                <p class="success">
                    <?= $success ?>
                </p>
            <?php endif; ?>
            <?= $event_name->render() ?>
            <div class="col-sm-6 col-lg-4">
                <?= $start_date->render() ?>
            </div>
            <div class="col-sm-6 col-lg-4">
                <?= $end_date->render() ?>
            </div>
            <div class="col-lg-4">
                <?= $limit_date->render() ?>
            </div>
            <div>
                <?= $bulletin_url->render() ?>
            </div>
        </div>
    </article>
</form>