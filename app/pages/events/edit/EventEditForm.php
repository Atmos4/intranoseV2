<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);

if ($event_id) {
    $event = em()->find(Event::class, $event_id);
    if ($event == null) {
        force_404("Error: the event with id $event_id does not exist");
    }
    $event_mapping = [
        "event_name" => $event->name,
        "start_date" => date_format($event->start_date, "Y-m-d"),
        "end_date" => date_format($event->end_date, "Y-m-d"),
        "limit_date" => date_format($event->deadline, "Y-m-d"),
        "bulletin_url" => $event->bulletin_url,
        "description" => $event->description
    ];
} else {
    $event = new Event();
}

$v = new Validator($event_mapping ?? []);
$event_name = $v->text("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date("start_date")->label("Date de départ")->required();
$end_date = $v->date("end_date")
    ->label("Date de retour")->required()
    ->min($start_date->value, "Doit être après le départ");
$limit_date = $v->date("limit_date")
    ->label("Deadline")->required()
    ->max($start_date->value ? date_create($start_date->value)->sub(new DateInterval("PT23H59M59S"))->format("Y-m-d") : "", "Doit être avant le jour de départ");
$bulletin_url = $v->url("bulletin_url")->label("Lien vers le bulletin")->placeholder();
$description = $v->textarea("description")->label("Description");

if ($v->valid()) {
    $event->set($event_name->value, $start_date->value, $end_date->value, $limit_date->value, $bulletin_url->value ?? "");
    $event->description = $description->value;
    em()->persist($event);
    em()->flush();
    redirect("/evenements/$event->id");
}

?>
<article>
    <div class="row">
        <?= $v->render_validation() ?>
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
        <?= $bulletin_url->render() ?>
        <?= $description->attributes(["rows" => "8"])->render() ?>
    </div>
</article>