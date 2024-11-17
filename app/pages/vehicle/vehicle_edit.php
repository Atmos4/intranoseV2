<?php
restrict_access();

$user = User::getCurrent();

$event_id = get_route_param("event_id");
$event = em()->find(Event::class, $event_id);
if (!$event) {
    force_404("the event of id $event_id doesn't exist");
}
$vehicle_id = get_route_param("vehicle_id", strict: false);
if ($vehicle_id) {
    $vehicle = em()->find(Vehicle::class, $vehicle_id);
    if ($vehicle == null) {
        force_404("Error: the vehicle with id $vehicle_id does not exist");
    }
    $vehicle_mapping = [
        "name" => $vehicle->name,
        "start_location" => $vehicle->start_location,
        "return_location" => $vehicle->return_location,
        "capacity" => $vehicle->capacity,
    ];
} else {
    $vehicle = new Vehicle();
}


$v = new Validator($vehicle_mapping ?? []);
$name = $v->text("name")->label("Nom du véhicule")->placeholder()->required();
$start_location = $v->text("start_location")->label("Lieu de départ")->placeholder()->required();
$return_location = $v->text("return_location")->label("Lieu de retour")->placeholder()->required();
$capacity = $v->number("capacity")->label("Capacité du véhicule")->min($vehicle_id ? count(($vehicle->passengers)) : 1)->placeholder()->required();


if ($v->valid()) {
    $vehicle->name = $name->value;
    $vehicle->start_location = $start_location->value;
    $vehicle->return_location = $return_location->value;
    $vehicle->manager = $user;
    $vehicle->event = $event;
    $vehicle->capacity = $capacity->value;
    em()->persist($vehicle);
    em()->flush();
    redirect("/evenements/$event->id");
}

page(($vehicle_id ? "Modifier le véhicule" : "Ajouter un véhicule") . " pour $event->name");
?>

<form method="post">
    <?= actions()?->back("/evenements/$event_id", "Annuler")->submit($vehicle_id ? "Modifier" : "Ajouter") ?>
    <article>
        <div class="row">
            <?= $v->render_validation() ?>
            <div class="col-sm-12 col-lg-6">
                <?= $name->render() ?>
            </div>
            <div class="col-sm-12 col-lg-6">
                <?= $start_location->render() ?>
            </div>
            <div class="col-sm-12 col-lg-6">
                <?= $return_location->render() ?>
            </div>
            <div class="col-sm-12 col-lg-6">
                <?= $capacity->render() ?>
            </div>
        </div>
    </article>
</form>