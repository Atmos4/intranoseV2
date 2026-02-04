<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$event = em()->find(Event::class, $event_id);
if (!$event) {
    force_404("the event of id $event_id doesn't exist");
}
if (!empty($_POST) and isset($_POST['change'])) {
    $event->type = EventType::Complex;
    em()->persist($event);
    em()->flush();
    redirect("/evenements/$event_id/modifier/complexe");
}

page("Changement de type d'événement");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir passer à du multi-activité ? Vous pouvez passer à un événement de type complexe pour avoir
            plusieurs activités, mais cette action est irréversible. Pour repasser à un événement "simple" mono-activité
            il faudra recréer un événement.</p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>/modifier/simple">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="change" value="true" class="destructive">Changer</button>
        </div>
    </div>
</form>