<?php
restrict_access(Access::$ADD_EVENTS);
$me_user = User::getMain();
$v = new Validator(action: "new_message");
$my_groups = em()->getRepository(UserGroup::class)->findBy([], ['name' => 'ASC']);
$group_options = [];
foreach ($my_groups as $g) {
    $group_options[$g->id] = $g->name;
}
$groups_select = $v->select("groups")->multiple()->options($group_options)->label("Groupes")->required("Choisissez au moins un groupe");
$input = $v->textarea("new_message")->placeholder("Message...")->attributes(["rows" => 10])->autocomplete("off");

if ($v->valid()) {
    $subject = "Nouveau message de groupe";
    $selected_groups = array_filter(
        $my_groups,
        fn($g) => in_array((string) $g->id, $groups_select->value, true)
    );
    $conversation = new Conversation();
    foreach ($selected_groups as $g) {
        $conversation->groups->add($g);
        $g->conversations->add($conversation);
    }
    em()->persist($conversation);
    $message = $conversation->sendMessage($me_user, $input->value);
    foreach ($selected_groups as $g) {
        MailerFactory::createGroupMessageEmail($g, $message, EventRecipientType::REGISTERED_USERS, $subject);
    }
    Toast::success("Message envoyé 🚀");
    redirect("/messages");
}

page("Nouveau message de groupe")->css("group_messages.css")->enableHelp();
?>

<?= actions()->back("/messages") ?>
<p><i class="fa fa-info-circle"></i> Ce message sera envoyé à tous les membres des groupes sélectionnés.</p>
<small>
    Le message doit être écrit en <a href='https://www.markdownguide.org/basic-syntax/' target="#">style
        markdown</a>
</small>
<form method="post">
    <?= $v ?>
    <?= $groups_select ?>
    <?= $input->reset() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>