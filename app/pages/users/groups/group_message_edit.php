<?php
restrict_access();
$group_id = get_route_param('group_id');
$group = em()->find(UserGroup::class, $group_id);
$me_user = User::getMain();
$v = new Validator(action: "new_message");
$input = $v->textarea("new_message")->placeholder("Message...")->attributes(["rows" => 10])->autocomplete("off");

if ($v->valid()) {
    $message = $group->conversation->sendMessage($me_user, $input->value);
    $subject = "Nouveau message de groupe";
    MailerFactory::createGroupMessageEmail($group, $message, EventRecipientType::REGISTERED_USERS, $subject);
    Toast::success("Message envoyé 🚀");
    redirect("/groupes/$group->id");
}

page("Nouveau message de groupe")->enableHelp();
?>

<?= actions()->back("/groupes/$group->id") ?>
<p><i class="fa fa-info-circle"></i> Ce message sera envoyé à tous les membres du groupe.</p>
<small>
    Le message doit être écrit en <a href='https://www.markdownguide.org/basic-syntax/' target="#">style
        markdown</a>
</small>
<form method="post">
    <?= $v ?>
    <?= $input->reset() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>