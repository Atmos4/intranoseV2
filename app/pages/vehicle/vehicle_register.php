<?php
restrict_access();

$user = User::getCurrent();
$vehicle_id = get_route_param("vehicle_id", true, true);
$user_id = get_route_param("user_id", true, true);
$event_id = get_route_param("event_id", true, true);
$vehicle = em()->find(Vehicle::class, $vehicle_id);
$event = em()->find(Event::class, $event_id);
$can_edit = check_auth(Access::$ADD_EVENTS);

if (!$vehicle) {
    return "Le véhicule en question n'existe pas.";
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "POST") {
    $is_in_passengers = in_array($user, $vehicle->passengers->getValues());

    if (!$is_in_passengers && count($vehicle->passengers) < $vehicle->capacity) {
        $vehicle->passengers->add($user);
        em()->persist($vehicle);
        em()->flush();
        Toast::success("Ajouté au véhicule");
    } elseif (!$is_in_passengers) {
        Toast::error("Le véhicule est complet");
    }

    if ($is_in_passengers) {
        $vehicle->passengers->removeElement($user);
        em()->persist($vehicle);
        em()->flush();
        Toast::error("Enlevé du véhicule");
    }
}

?>
<article class="vehicle-article">
    <details <?= $method == "POST" ? "open" : "" ?>>
        <summary>
            <div class="vehicle-name">
                <?= $vehicle->name ?>
            </div>
            <div class="location">
                <span>
                    <?= $vehicle->start_location ?>
                </span>
                <i class="fas fa-arrow-right"></i>
                <span>
                    <?= $vehicle->start_location ?>
                </span>
            </div>
            <div class="capacity">
                <i class="fas fa-chair"></i> <?= count($vehicle->passengers) ?> / <?= $vehicle->capacity ?>
            </div>
        </summary>

        <div class="grid">
            <div>
                <h6>Passagers</h6>
                <?php if (!$vehicle->passengers[0]): ?>
                    Pas de passagers pour l'instant 🚘
                <?php endif ?>
                <p class="passenger-flex">
                    <?php foreach ($vehicle->passengers as $i => $passenger): ?>
                        <a href="/licencies?user=<?= $user_id ?>" <?= UserModal::props($user_id) ?>><i
                                class="fas fa-user"></i>
                            <?= $passenger->first_name ?>     <?= $passenger->last_name ?> </a>
                    <?php endforeach ?>
                </p>
            </div>
            <div>
                <h6>Responsable</h6>
                <p>
                    <a href="/licencies?user=<?= $user_id ?>" <?= UserModal::props($user_id) ?>><i
                            class="fas fa-user"></i>
                        <?= $vehicle->manager->first_name ?>
                        <?= $vehicle->manager->last_name ?>
                    </a>
                </p>
            </div>
        </div>
        <div class="buttons-grid">
            <?php if ($vehicle->passengers->contains($user)): ?>
                <a role="button" class="outline secondary"
                    hx-post="/evenements/<?= $event->id ?>/vehicule/<?= $vehicle->id ?>/inscription/<?= $user_id ?>">
                    <i class="fa fa-trash"></i>
                    S'enlever</a>
            <?php else: ?>
                <a role="button" class="outline"
                    hx-post="/evenements/<?= $event->id ?>/vehicule/<?= $vehicle->id ?>/inscription/<?= $user_id ?>">
                    <i class="fa fa-plus"></i>
                    S'ajouter</a>
            <?php endif ?>
            <?php if ($can_edit): ?>
                <a role="button" class="outline secondary" href="/evenements/<?= $event->id ?>/vehicule/<?= $vehicle->id ?>"
                    hx-target="body">
                    <i class="fa fa-pen"></i>
                    Modifier</a>
                <a role="button" class="outline error"
                    href="/evenements/<?= $event->id ?>/vehicule/<?= $vehicle->id ?>/supprimer" hx-target="body">
                    <i class="fa fa-trash"></i>
                    Supprimer</a>
            <?php endif ?>
        </div>
    </details>
</article>