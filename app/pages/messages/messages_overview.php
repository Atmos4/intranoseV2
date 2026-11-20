<?php
restrict_access();
$me_user = User::getMain();
$row = MessageService::getAllGroupMessages();
page("Messages de groupe")->css("group_messages.css");
?>
<?php if (check_auth(Access::$ADD_EVENTS)): ?>
    <?= actions()->link("/messages/nouveau", "Nouveau", "fa-plus") ?>
<?php endif ?>

<?php if (!$row): ?>
    <p class="center">
        Pas de messages pour le moment
    </p>
<?php endif;
foreach ($row as $conversation): ?>
    <?= MessageService::renderGroupFeed($conversation) ?>
<?php endforeach;