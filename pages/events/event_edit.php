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
        "limit_date" => date_format($event->deadline, "Y-m-d")

    ];
} else {
    $event = new Event();
}

$v = new Validator($event_mapping ?? []);
$event_name = $v->text("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date("start_date")->label("Date de départ")->required();
$end_date = $v->date("end_date")
    ->label("Date de retour")->required()
    ->after($start_date->value, "Doit être après le départ");
$limit_date = $v->date("limit_date")
    ->label("Deadline")->required()
    ->before(date_create($start_date->value ?? "")->sub(new DateInterval("PT23H59M59S"))->format("Y-m-d"), "Doit être avant le jour de départ");

$v2 = new Validator();
$file_upload = $v2->upload("file_upload")->label("Circulaire");

if (!empty($_POST) && $v->valid()) {
    $event->set($event_name->value, $start_date->value, $end_date->value, $limit_date->value);
    em()->persist($event);
    em()->flush();
    redirect("/evenements/$event->id");
}

if (!empty($_FILES) && $v2->valid()) {
    $date = date('Y-m-d h:i:s');
    if (set_file($event_id, $file_upload->get_name(), $date, $file_upload->get_size(), $file_upload->get_type())) {
        $success = $file_upload->save_file();
    }

}

page($event_id ? "{$event->name} : Modifier" : "Créer un événement");
?>
<form method="post">
    <nav id="page-actions">
        <a href="/evenements<?= $event_id ? "/$event_id" : "" ?>" class="secondary">
            <i class="fas fa-caret-left"></i> Annuler
        </a>
        <div>
            <button type="submit">
                <?= $event_id ? "Modifier" : "Créer" ?>
            </button>
        </div>
    </nav>
    <article>
        <div class="row">
            <?= $v->render_validation() ?>
            <?= $v2->render_validation() ?>
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
        </div>
    </article>
</form>
<?php /*
 <form method="post" enctype="multipart/form-data">
 <div class="center">
 <?= $file_upload->render() ?>
 </div>
 <div>
 <button type="submit">
 Enregistrer
 </button>
 </div>
 </form> */?>
</article>