<?php
restrict_access();
$group_id = get_route_param('group_id');
$group = em()->find(UserGroup::class, $group_id);
$me_user = User::getMain();

# the first time the message page is visited, a conversation is created
if (!$group->conversation?->id) {
    $conversation = new Conversation();
    $group->conversation = $conversation;
    em()->persist($group);
    em()->flush();
}

$messages = $group->conversation->getMessages();

?>

<?= MessageService::renderMessages($messages, $me_user, $group->id, MessageSourceType::GROUP) ?>