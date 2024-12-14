<?php
restrict_access();
$user_id = User::getCurrent()->id;
$event = EventService::getEventWithAllData(get_route_param('event_id'), $user_id);

$vehicles = em()->createQuery('SELECT v FROM Vehicle v WHERE v.event = ?1')->setParameter(
    1,
    $event->id
)->getResult();

?>

<?php foreach ($vehicles as $vehicle): ?>
    <div hx-get="/evenements/<?= $event->id ?>/vehicule/<?= $vehicle->id ?>/inscription/<?= $user_id ?>" hx-trigger="load"
        hx-target="this">
    </div>
<?php endforeach ?>

<a role=button class="secondary" href="/evenements/<?= $event->id ?>/vehicule/nouveau"
    data-intro="Il est possible d'ajouter votre véhicule pour covoiturer ! 🏎️">
    <i class="fas fa-plus"></i> Ajouter un véhicule</a>