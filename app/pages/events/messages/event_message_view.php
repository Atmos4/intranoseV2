<?php
restrict_access();
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);

if (!$event->open): ?>
    <p class="center">
        <?= "L'évenement n'est pas encore ouvert 🙃" ?>
    </p>
<?php else:
    $me_user = User::getMain();

    # the first time the message page is visited, a conversation is created
    if (!$event->conversation?->id) {
        $conversation = new Conversation();
        $event->conversation = $conversation;
        em()->persist($event);
        em()->flush();
    }

    $messages = $event->conversation->getMessages();
    ?>

    <?= MessageService::renderMessages($messages, $me_user, $event->id, MessageSourceType::EVENT) ?>
<?php endif ?>