<?php
restrict_access(Access::$ADD_EVENTS);
$me_user = User::getMain();
$conversation = em()->find(Conversation::class, Router::getParameter("conversation_id"));
if (!$conversation) {
    force_404("Conversation not found");
}
$back = "/messages/conversation/" . $conversation->id;

$v = new Validator(action: "reply_message");
$input = $v->textarea("new_message")->placeholder("Message...")->attributes(["rows" => 10])->autocomplete("off");

if ($v->valid()) {
    $message = $conversation->sendMessage($me_user, $input->value);
    foreach ($conversation->groups as $g) {
        MailerFactory::createGroupMessageEmail($g, $message, EventRecipientType::REGISTERED_USERS, "Nouveau message de groupe");
    }
    Toast::success("Message envoyé 🚀");
    redirect($back);
}

page("Nouveau message")->css("group_messages.css");
?>
<?= actions()->back($back) ?>
<p><i class="fa fa-info-circle"></i> Ce message sera envoyé à tous les membres des groupes suivants :</p>
<?php MessageService::renderGroupChips($conversation->groups) ?>
<br>
<small>
    Le message doit être écrit en <a href='https://www.markdownguide.org/basic-syntax/' target="#">style
        markdown</a>
</small>
<form method="post">
    <?= $v ?>
    <?= $input->reset() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i></button>
</form>
