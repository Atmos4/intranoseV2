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

    $v_delete = new Validator(action: "delete_message");
    if ($v_delete->valid() && isset($_POST['message_id'])) {
        $message = em()->find(Message::class, $_POST['message_id']);
        if ($message && ($message->sender == $me_user || check_auth(Access::$EDIT_USERS))) {
            em()->remove($message);
            em()->flush();
        }
    }

    if (!$event->conversation?->id) {
        $conversation = new Conversation();
        $event->conversation = $conversation;
        em()->persist($event);
        em()->flush();
    }
    $messages = $event->conversation->getMessages();
    ?>

    <div id="conversation">
        <?php if (check_auth(Access::$ADD_EVENTS)): ?>
            <p>
                <a role=button class="secondary" href="/evenements/<?= $event->id ?>/message/nouveau"
                    data-intro="Vous pouvez ajouter un message d'évenement ici ! 💭">
                    <i class="fas fa-plus"></i> Ajouter un message
                </a>
            </p>
        <?php endif ?>
        <?php foreach ($messages as $message):
            $fromMe = $message->sender == $me_user; ?>
            <article>
                <?= (new Parsedown)->text($message->content) ?>
                <footer style="display: flex;flex-direction: column;">
                    <small>
                        <?= $fromMe ? "Moi" : $message->sender->first_name ?>
                    </small>
                    <small>
                        <?= $message->sentAt->format("d/m H:i") ?>
                    </small>
                </footer>
            </article>
        <?php endforeach ?>

    </div>
<?php endif ?>