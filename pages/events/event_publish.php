<?php
restrict_access("COACH", "STAFF", "ROOT", "COACHSTAFF");

require_once("database/events.api.php");
$event_id = get_route_param("event_id");
$event = get_event_by_id($event_id);

if (!empty($_POST) and isset($_POST['publish'])) {
    $result = publish_event($event_id, $event['open'] ? 0 : 1);
    if ($result) {
        redirect("/evenements/$event_id");
    }
}

page(($event['open'] ? "Retirer" : "Publier") . " - {$event['nom']}");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir <?= $event['open'] ? "retirer" : "publier" ?> l'événement <?= $event['nom'] ?>?</p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="publish" value="true"><?= $event['open'] ? "Retirer" : "Publier" ?></button>
        </div>
    </div>
</form>