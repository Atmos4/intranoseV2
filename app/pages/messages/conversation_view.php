<?php
restrict_access();
$me_user = User::getMain();
$conversation = em()->find(Conversation::class, Router::getParameter("conversation_id"));
if (!$conversation) {
    force_404("Conversation not found");
}

$group_names = $conversation->groups->map(fn($g) => $g->name)->toArray();

$title = $conversation->name ?: ($group_names ? implode(", ", $group_names) : "Conversation");
$messages = $conversation->getMessages();

page($title)->css("group_messages.css")->script("copy-entry-emails.js");

$can_manage = check_auth(Access::$ADD_EVENTS);
$actions = actions()->back("/messages");
if ($can_manage) {
    $string_email_list = MessageService::getConversationEmailList($conversation);
    $actions->dropdown(fn($d) => $d->link("/messages/conversation/{$conversation->id}/renommer", "Renommer la conversation", "fa-pen")
        ->link("#", "Copier la liste d'emails", "fa-clipboard", [
            "onclick" => "copyEntryEmails(" . htmlspecialchars(json_encode($string_email_list), ENT_QUOTES) . ")",
            "role" => "link",
        ]));
}
?>
<?= $actions ?>
<p><i class="fa fa-info-circle"></i> Messages envoyés aux groupes suivants :</p>
<?php
MessageService::renderGroupChips($conversation->groups);
MessageService::renderMessages($messages, $me_user, "/messages/conversation/{$conversation->id}/nouveau");
