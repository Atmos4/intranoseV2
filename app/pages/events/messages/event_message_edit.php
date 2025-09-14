<?php
restrict_access();
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);
$me_user = User::getMain();
$v = new Validator(action: "new_message");
$input = $v->textarea("new_message")->placeholder("Message...")->attributes(["rows" => 10])->autocomplete("off");
$recipients = $v->text("recipients")->label("Destinataires");

if ($v->valid()) {
    logger()->debug("Formular valid");
    $message = $event->conversation->sendMessage($me_user, $input->value);
    MailerFactory::createEventMessageEmail($event, $message, $me_user, RecipientType::from($recipients->value));
    redirect("/evenements/$event->id");
    Toast::success("Message envoyé 🚀");
}

page("Nouveau message d'évenement")->enableHelp();
?>

<?= actions()->back("/evenements/$event->id") ?>
<small>
    Le message doit être écrit en <a href='https://www.markdownguide.org/basic-syntax/' target="#">style
        markdown</a>
</small>
<form method="post">
    <?= $v ?>
    <?= $input->reset() ?>
    <fieldset>
        <legend>
            Destinataires:
        </legend>
        <?php if (!$event->groups->isEmpty()): ?>
            <label>
                <input type="radio" name="recipients" value="<?= RecipientType::EVENT_GROUPS->value ?>" required />
                Groupes de l'événement
                <p><?= GroupService::renderTags($event->groups) ?></p>
            </label>
        <?php endif ?>
        <label>
            <input type="radio" name="recipients" value="<?= RecipientType::REGISTERED_USERS->value ?>" required />
            Inscrits à l'événement
        </label>
        <label>
            <input type="radio" name="recipients" value="<?= RecipientType::UNREGISTERED_USERS->value ?>" required />
            Sans réponse à l'événement
            <?php if (!$event->groups->isEmpty()): ?>
                (groupes)
                <p><?= GroupService::renderTags($event->groups) ?></p>
            <?php endif ?>
        </label>
        <label>
            <input type="radio" name="recipients" value="<?= RecipientType::ALL_USERS->value ?>" required />
            Tout le club
        </label>
    </fieldset>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>