<?php
restrict_access();
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);
$me_user = User::getMain();

$v = new Validator(action: "new_message");
$input = $v->text("new_message")->placeholder("Message...")->autocomplete("off");

if ($v->valid()) {
    $event->conversation->sendMessage($me_user, $input->value);
}

$v_delete = new Validator(action: "delete_message");
if ($v_delete->valid() && isset($_POST['message_id'])) {
    $message = em()->find(Message::class, $_POST['message_id']);
    if ($message && ($message->sender == $me_user || check_auth(Access::$EDIT_USERS))) {
        em()->remove($message);
        em()->flush();
    }
}

$conversation = isset($event->conversation) ? $event->conversation : null;
if (!$conversation) {
    $conversation = new Conversation();
    $event->conversation = $conversation;
    em()->persist($conversation);
    em()->persist($event);
}
$messages = $conversation->getMessages();
?>

<div id="conversation">
    <section class="messages">
        <?php foreach ($messages as $message):
            $fromMe = $message->sender == $me_user; ?>

            <div class="<?= $fromMe ? "message me" : "message other" ?>">
                <div class="button-delete">
                    <form method="post" class="button-delete" hx-post="/evenements/<?= $event->id ?>/conversation"
                        hx-target="#conversation">
                        <?= $v_delete ?>
                        <input type="hidden" name="message_id" value="<?= $message->id ?>">
                        <button role="link"><i class="fa fa-trash"></i></button>
                    </form>
                </div>
                <div class="content">
                    <?= $message->content ?>
                </div>
                <div class="info">
                    <div>
                        <?= $fromMe ? "Moi" : $message->sender->first_name ?>
                    </div>
                    <div>
                        <?= $message->sentAt->format("H:i") ?>
                    </div>
                </div>
            </div>

        <?php endforeach ?>

    </section>
    <?php if (check_auth(Access::$ADD_EVENTS)): ?>
        <form role="group" method="post" hx-post="/evenements/<?= $event->id ?>/conversation" hx-target="#conversation">
            <?= $v ?>
            <?= $input->reset() ?>
            <button><i class="fa fa-paper-plane"></i></button>
        </form>
    <?php endif ?>
</div>