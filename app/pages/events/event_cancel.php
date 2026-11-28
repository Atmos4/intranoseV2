<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param("event_id");
$event = em()->find(Event::class, $event_id);
if (!$event) {
    echo "the event of id $event_id doesn't exist";
    return;
}

$v = new Validator(["send_email" => 1]);
$send_email = $v->switch("send_email")->label(
    $event->groups->isEmpty()
    ? "Envoyer un mail à tout le club (pas de groupe associé à l'événement) ?"
    : "Envoyer un mail aux groupes concernés ?"
);

if ($v->valid()) {
    $event->is_cancelled = !$event->is_cancelled;
    em()->persist($event);
    em()->flush();
    if ($event->is_cancelled && $send_email->value) {
        MailerFactory::createEventCancellationEmail($event)->send();
    }
    $event->is_cancelled ? Toast::error("Événement annulé") : Toast::success("Événement rétabli");
    redirect("/evenements/$event->id");
}

page(($event->is_cancelled ? "Rétablir" : "Annuler") . " l'événement {$event->name}")->heading("Attention");
?>
<form method="post">
    <?= $v ?>
    <div class="row center">
        <p>Sûr de vouloir
            <?= $event->is_cancelled ? "rétablir" : "annuler" ?> l'événement
            <strong>
                <?= $event->name ?>
            </strong>
            <i class="fa fa-question"></i>

        </p>
        <p>
            <?php if ($event->is_cancelled): ?>
                L'événement sera alors de nouveau affiché normalement et ouvert aux inscriptions.
            <?php else: ?>
                L'événement sera alors montré comme annulé et fermé aux inscriptions.
            <?php endif ?>
        </p>
        <?php if (!$event->is_cancelled): ?>
            <div class="col-12" style="display:flex;flex-direction:column;align-items:center">
                <?= GroupService::renderTags($event->groups) ?>
                <?= $send_email->render() ?>
            </div>
        <?php endif ?>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>">Retour</a>
        </div>
        <div class="col-auto">
            <button type="submit" <?= $event->is_cancelled ? " class='contrast'" : "" ?>>
                <?= $event->is_cancelled ? "Rétablir" : "Annuler" ?>
            </button>
        </div>
    </div>
</form>