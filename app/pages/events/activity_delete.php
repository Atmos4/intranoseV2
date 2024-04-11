<?php
restrict_access(Access::$ADD_EVENTS);

$activity_id = get_route_param("activity_id");
$event_id = get_route_param("event_id");
$activity = em()->find(Activity::class, $activity_id);
if (!$activity) {
    echo "the activity of id $activity_id doesn't exist";
    return;
}
if (!empty($_POST) and isset($_POST['delete'])) {
    em()->remove($activity);
    em()->flush();
    redirect("/evenements/$event_id");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir supprimer cette activité?</p>
        <p class="row">
            <span>
                <i class="fa fa-chevron-right"></i>
                Inscriptions:
                <?= count($activity->entries) ?>
            </span>
        </p>
        <div class="col-auto">
            <a class="secondary" role="button"
                href="/evenements/<?= $event_id ?>/activite/<?= $activity_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>