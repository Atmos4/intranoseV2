<?php
restrict_access();
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);
$me_user = User::getMain();
$v = new Validator(action: "new_message");

$message_text = <<<EOL
Bonjour à tous,

Rappel, pensez à vous inscrire !

L'équipe d'organisation. 
EOL;

$input = $v->textarea("new_message")->placeholder("Message...")->attributes(["rows" => 10])->autocomplete("off");
$input->value = $message_text;

if ($v->valid()) {
    $message = $event->conversation->sendMessage($me_user, $input->value);
    MailerFactory::createEventMessageEmail($event, $message, RecipientType::UNREGISTERED_USERS, "RAPPEL d'inscription");
    Toast::success("Rappel envoyé 💭");
    redirect("/evenements/$event->id");
}

page("Nouveau rappel")->enableHelp();
?>

<?= actions()->back("/evenements/$event->id") ?>
<p><i class="fa fa-info-circle"></i> Ce message sera envoyé à tous les utilisateurs non inscrits à l'événement et
    appartenants aux groupes concernés, et par défaut à tous les utilisateurs non inscrits si l'événement n'est affilié
    à aucun groupe.</p>
<small>
    Le message doit être écrit en <a href='https://www.markdownguide.org/basic-syntax/' target="#">style
        markdown</a>
</small>
<form method="post">
    <?= $v ?>
    <?= $input ?>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>