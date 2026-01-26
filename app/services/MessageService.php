<?php

class MessageService
{

    public static function renderMessages(array $messages, User $me_user, string $id, MessageSourceType $type)
    {

        ?>
        <div id="conversation">
            <?php if (check_auth(Access::$ADD_EVENTS)): ?>
                <p>
                    <a role=button class="secondary" href="/<?= $type->toFrench() ?>/<?= $id ?>/message/nouveau"
                        data-intro="Vous pouvez ajouter un message ici ! 💭">
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
        <?php
    }
}

enum MessageSourceType: string
{
    case EVENT = "EVENT";
    case GROUP = "GROUP";
    function toFrench()
    {
        return match ($this) {
            self::EVENT => "evenements",
            self::GROUP => "groupes"
        };
    }
}