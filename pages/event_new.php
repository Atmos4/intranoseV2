<?php
restrict_access("ROOT", "STAFF", "COACH");

require_once "database/event_new.api.php";
$result = create_event($_POST);

page("Ajouter un événement");
?>
<div class="page-actions">
    <a href="/evenements" class="secondary"><i class="fas fa-xmark"></i> Annuler</a>
</div>
<form method="post">
    <article class="row">
        <label for="eventName">
            Nom de l'événement <?= $result?->render_error("event_name") ?>
            <input type="text" id="eventName" name="event_name" placeholder="Nom de l'événement" <?= $result?->render_input("event_name") ?>>
        </label>
        <label class="col-sm-6 col-lg-4" for="startDate">
            Date de départ <?= $result?->render_error("start_date") ?>
            <input type="date" id="startDate" name="start_date" required <?= $result?->render_input("start_date") ?>>
        </label>
        <label class="col-sm-6 col-lg-4" for="endDate">
            Date de retour <?= $result?->render_error("end_date") ?>
            <input type="date" id="endDate" name="end_date" required <?= $result?->render_input("end_date") ?>>
        </label>
        <label class="col-lg-4" for="limitDate">
            <b><i class="fas fa-clock"></i> Date limite</b> <?= $result?->render_error("limit_date") ?>
            <input type="date" id="limitDate" name="limit_date" required <?= $result?->render_input("limit_date") ?>>
        </label>
        <div>
            <button type="submit">Créer</button>
        </div>
    </article>
</form>