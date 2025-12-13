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
$recipient_type = $v->select("recipient_type")->options(RecipientType::options($event->groups->isEmpty()))->label("Destinataires");

if ($v->valid()) {
    $message = $event->conversation->sendMessage($me_user, $input->value);
    MailerFactory::createEventMessageEmail($event, $message, RecipientType::from($recipient_type->value), "RAPPEL d'inscription");
    Toast::success("Rappel envoyé 💭");
    redirect("/evenements/$event->id");
}

page("Nouveau rappel pour : " . $event->name)->enableHelp();
?>

<?= actions()->back("/evenements/$event->id") ?>
<form method="post">
    <?= $v ?>
    <?= $recipient_type ?>
    <small>Groupes de l'événement : <?= GroupService::renderTags($event->groups, is_div: false) ?></small>
    <hr>
    <small>
        Le message doit être écrit en <a href='https://www.markdownguide.org/basic-syntax/' target="#">style
            markdown</a>
    </small>
    <?= $input ?>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>