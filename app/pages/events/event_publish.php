<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param("event_id");
$event = em()->find(Event::class, $event_id);
if (!$event) {
    echo "the event of id $event_id doesn't exist";
    return;
}

if (!empty($_POST) and isset($_POST['publish'])) {
    $event->open = !$event->open;
    em()->persist($event);
    em()->flush();
    if ($event->open && isset($_POST['send_email'])) {
        MailerFactory::createEventPublicationEmail($event)->send();
    }
    #redirect("/evenements/$event->id");
}

page(($event->open ? "Retirer" : "Publier") . " - {$event->name}")->heading("Attention")->css("event_publish.css");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir
            <?= $event->open ? "retirer" : "publier" ?> l'événement
            <strong>
                <?= $event->name ?>
            </strong>
            <i class="fa fa-question"></i>

        </p>
        <p>
            <?php if ($event->open): ?>
                L'événement sera alors invisible et fermé aux inscriptions.
            <?php else: ?>
                L'événement sera alors visible et ouvert aux inscriptions.
            <?php endif ?>
        </p>
        <?php if (!$event->open): ?>
            <p>
                <label>
                    <input name="send_email" type="checkbox" role="switch" checked />
                    Je veux notifier les licenciés
                </label>
            </p>
        <?php endif ?>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" <?= $event->open ? " class='contrast'" : "" ?> name="publish" value="true">
                <?= $event->open ? "Retirer" : "Publier" ?>
            </button>
        </div>
    </div>
</form>