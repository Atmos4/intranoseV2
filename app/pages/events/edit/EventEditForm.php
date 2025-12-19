<?php
restrict_access(Access::$ADD_EVENTS);
$event_type = get_query_param("type", false, false);
$event_id = get_route_param("event_id", strict: false);

if ($event_type && $event_type == "simple") {
    import(__DIR__ . "/ActivityEditForm.php")($event_id, is_simple: true, post_link: "/evenements" . ($event_id ? "/$event_id" : "") . "/event_form?type=simple");
    return;
}

if ($event_id) {
    $event = em()->find(Event::class, $event_id);
    if ($event == null) {
        force_404("Error: the event with id $event_id does not exist");
    }
    $event_mapping = [
        "event_name" => $event->name,
        "start_date" => date_format($event->start_date, "Y-m-d H:i:s"),
        "end_date" => date_format($event->end_date, "Y-m-d H:i:s"),
        "limit_date" => date_format($event->deadline, "Y-m-d H:i:s"),
        "bulletin_url" => $event->bulletin_url,
        "description" => $event->description
    ];
} else {
    $event = new Event();
}

$v = new Validator($event_mapping ?? []);
$event_name = $v->text("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date_time("start_date")->label("Date de départ")->required();
$end_date = $v->date_time("end_date")
    ->label("Date de retour")->required()
    ->min($start_date->value, "Doit être après le départ");
$limit_date = $v->date_time("limit_date")
    ->label("Deadline")->required()
    ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de départ");
$bulletin_url = $v->url("bulletin_url")->label("Lien vers le bulletin")->placeholder();
$description = $v->textarea("description")->label("Description");

if ($v->valid()) {
    $event->set($event_name->value, $start_date->value, $end_date->value, $limit_date->value, $bulletin_url->value ?? "");
    $event->type = EventType::Complex;
    $event->description = $description->value;
    GroupService::processEventGroupChoice($event);
    em()->persist($event);
    em()->flush();
    Toast::success("Enregistré");
    redirect("/evenements/$event->id");
}

?>
<form method="post" hx-post="/evenements/<?= $event_id ?>/event_form">
    <?= actions()?->back("/evenements" . ($event_id ? "/$event_id" : ""), "Annuler", "fas fa-xmark")->submit($event_id ? "Modifier" : "Créer") ?>
    <article class="row">
        <?= $v->render_validation() ?>
        <?= $event_name->render() ?>
        <div class="col-sm-6 col-lg-4">
            <?= $start_date->render() ?>
        </div>
        <div class="col-sm-6 col-lg-4">
            <?= $end_date->render() ?>
        </div>
        <div class="col-lg-4" data-intro="Au delà de la deadline, les utilisateurs ne peuvent plus s'inscrire">
            <?= $limit_date->render() ?>
        </div>
        <div data-intro="Vous pouvez ajouer un lien vers un bulletin en ligne"><?= $bulletin_url->render() ?></div>
        <div
            data-intro="Vous pouvez formatter le texte de la description en markdown. N'hésitez pas à aller voir <a href='https://www.markdownguide.org/' target='_blank'>cette ressource</a>">
            <?= $description->attributes(["rows" => "8"])->render() ?>
        </div>
        <?= GroupService::renderEventGroupChoice($event) ?>
    </article>
</form>