<?php
// this page is supposed to be for private conversations only, so we will safeguard the usage.
$other_user_id = Router::getParameter("user_id");
$other_user = User::get($other_user_id);
$me_user = User::getMain();

$conversation = Conversation::upsertPrivateConversation($me_user, $other_user);

$v = new Validator();
$input = $v->text("new_message")->placeholder("Message...")->autocomplete("off");

if ($v->valid()) {
    $conversation->sendMessage($me_user, $input->value);
    header("Hx-Trigger: newMessageSent");
}

$has_firebase_updates = !!env("FIREBASE_API_KEY");

$messages = $conversation->getMessages();

page("Messages")->heading(false)->noPadding()->css("messages.css") ?>
<div class="fullheight-wrapper">
    <h2 class="center">
        <?= "$other_user->first_name $other_user->last_name" ?>
    </h2>
    <?= actions()->back("/messages") ?>
    <section class="messages" hx-get="/messages/direct/<?= $other_user_id ?>" hx-select=".messages"
        hx-target=".messages" hx-trigger="messagesUpdated" hx-swap="outerHTML scroll:bottom">
        <?php foreach ($messages as $message):
            $fromMe = $message->sender == $me_user; ?>

            <div class="<?= $fromMe ? "message me" : "message other" ?>">
                <div class="content">
                    <?= $message->content ?>
                </div>
                <div class="info">
                    <div>
                        <?= $fromMe ? "Moi" : $other_user->first_name ?>
                    </div>
                    <div>
                        <?= $message->sentAt->format("H:i") ?>
                    </div>
                </div>
            </div>

        <?php endforeach ?>

    </section>
    <?= $has_firebase_updates ? "" : "The firebase updater is not configured. This chat won't reload when new messages come in." ?>
    <form role="group" method="post">
        <?= $v ?>
        <?= $input->reset() ?>
        <button><i class="fa fa-paper-plane"></i></button>
    </form>
</div>
<script>
    !function () {
        const messagesContainer = document.querySelector('.messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }()
</script>
<?php if ($has_firebase_updates)
    include __DIR__ . "/firebase_updater.php" ?>