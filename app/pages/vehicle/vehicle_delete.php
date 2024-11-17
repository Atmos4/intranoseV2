<?php
restrict_access();

$form = new Validator(action: "confirm-delete");

$event_id = get_route_param("event_id");
$vehicle_id = get_route_param("vehicle_id");
$event = em()->find(Event::class, $event_id);
$vehicle = em()->find(Vehicle::class, $vehicle_id);
if (!$event) {
    force_404("the event of id $event_id doesn't exist");
}
if (!$vehicle) {
    force_404("the vehicle of id $vehicle_id doesn't exist");
}
if ($form->valid()) {
    logger()->info("Véhicule {vehicle_id} deleted by user {currentUserLogin}", ['vehicle_id' => $vehicle_id, 'currentUserLogin' => User::getCurrent()->login]);
    em()->remove($vehicle);
    em()->flush();
    Toast::error("Véhicule supprimé");
    redirect("/evenements/$event_id");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Sûr de vouloir supprimer le véhicule
            <?= "$vehicle->id" ?> ? Il sera définitivement supprimé!!
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>