<?php
restrict_access("COACH", "STAFF", "ROOT", "COACHSTAFF");

$event_id = get_route_param("event_id");
require_once("database/events.api.php");
$event = get_event_by_id($event_id);
if ($event['open']) {
    force_404("Cannot delete published event");
}
if (!empty($_POST) and isset($_POST['delete'])) {
    $result = delete_event($event_id);
    if ($result) {
        redirect("/evenements");
    }
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir supprimer?</p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>