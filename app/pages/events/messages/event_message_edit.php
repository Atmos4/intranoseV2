<?php
restrict_access();
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);
$me_user = User::getMain();
$v = new Validator(action: "new_message");
$input = $v->textarea("new_message")->placeholder("Message...")->attributes(["rows" => 10])->autocomplete("off");

if ($v->valid()) {
    $event->conversation->sendMessage($me_user, $input->value);
    redirect("/evenements/$event->id");
}

page("Nouveau message d'évenement")->enableHelp();
?>

<?= actions()->back("/evenements/$event->id") ?>
<form method="post">
    <?= $v ?>
    <?= $input->reset() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>