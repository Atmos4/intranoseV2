<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$event = em()->find(Event::class, $event_id);
if (!$event) {
    force_404("the event of id $event_id doesn't exist");
}
if ($event->open) {
    force_404("Cannot delete published event");
}
if (!empty($_POST) and isset($_POST['delete'])) {
    em()->remove($event);
    em()->flush();
    redirect("/evenements");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir supprimer cet événement?</p>
        <p class="row">
            <span>
                <i class="fa fa-chevron-right"></i>
                Courses:
                <?= count($event->activities) ?>
            </span>
            <span>
                <i class="fa fa-chevron-right"></i>
                Inscriptions:
                <?= count($event->entries) ?>
            </span>
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>