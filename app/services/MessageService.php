<?php

class MessageService
{
    /** @param string|null $addUrl link to the "new message" page; null hides the button */
    public static function renderMessages(array $messages, User $me_user, ?string $addUrl = null)
    {

        ?>
        <div id="conversation">
            <?php if ($addUrl && check_auth(Access::$ADD_EVENTS)): ?>
                <p>
                    <a role=button class="secondary" href="<?= $addUrl ?>"
                        data-intro="Vous pouvez ajouter un message ici ! 💭">
                        <i class="fas fa-plus"></i> Ajouter un message
                    </a>
                </p>
            <?php endif ?>
            <?php foreach ($messages as $message):
                $fromMe = $message->sender == $me_user; ?>
                <article>
                    <?= (new Parsedown())->text($message->content) ?>
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
    /** @return Conversation[] conversations linked to at least one group, with their groups loaded */
    public static function getAllGroupMessages(int $limit = 50): array
    {
        return em()->createQuery(
            "SELECT c, g FROM Conversation c JOIN c.groups g ORDER BY c.id DESC"
        )->getResult();
    }
    /**
     * Comma-separated emails of all members of the conversation's groups,
     * plus the family leaders of members who are not family leaders themselves.
     */
    public static function getConversationEmailList(Conversation $conversation): string
    {
        $group_ids = $conversation->groups->map(fn($g) => $g->id)->toArray();
        if (!$group_ids) {
            return "";
        }

        $user_emails = em()->createQuery('
        SELECT DISTINCT u.real_email
        FROM User u
        JOIN u.groups g
        WHERE g.id IN (:gids) AND u.real_email IS NOT NULL AND u.real_email != \'\'
        ')
            ->setParameter("gids", $group_ids)
            ->getSingleColumnResult();

        $family_leader_emails = em()->createQuery('
        SELECT DISTINCT fl.real_email
        FROM User u
        JOIN u.groups g
        JOIN User fl WITH fl.family = u.family
        WHERE g.id IN (:gids)
        AND u.family_leader = false
        AND fl.family_leader = true
        AND fl.real_email IS NOT NULL AND fl.real_email != \'\'
        ')
            ->setParameter("gids", $group_ids)
            ->getSingleColumnResult();

        return implode(',', array_unique(array_merge($user_emails, $family_leader_emails)));
    }
    /** @param iterable<UserGroup> $groups */
    public static function renderGroupChips(iterable $groups)
    {
        $colors = ThemeColor::colorsList(); ?>
        <div class="group-chips">
            <?php foreach ($groups as $group): ?>
                <span class="group-chip" style="--group-color: <?= $colors[$group->color->value] ?>">
                    <span class="group-dot"></span>
                    <?= htmlspecialchars($group->name) ?>
                </span>
            <?php endforeach ?>
        </div>
        <?php
    }
    public static function renderGroupFeed(Conversation $conversation)
    {
        $conversation_id = $conversation->id;
        $first_group = $conversation->groups->first();
        $all_groups = $conversation->groups->toArray();
        $visible = array_slice($all_groups, 0, 3);
        $hidden = array_slice($all_groups, 3);
        $color = ThemeColor::colorsList()[$first_group->color->value];
        $message = em()->createQuery(
            "SELECT m, s FROM Message m JOIN m.sender s
             WHERE m.conversation = :conv
             ORDER BY m.sentAt DESC"
        )
            ->setParameter("conv", $conversation_id)
            ->setMaxResults(1)
            ->getOneOrNullResult();
        if (!$message) {
            return;
        } ?>
            <article class="group-message"
                hx-trigger="click,keyup[key=='Enter'||key==' ']" hx-get="/messages/conversation/<?= $conversation_id ?>"
                hx-target="body" hx-push-url="true" tabindex=0>
                <?php if ($conversation->name): ?>
                    <strong><?= htmlspecialchars($conversation->name) ?></strong>
                <?php endif ?>
                <div class="group-chips">
                    <?php foreach ($visible as $group): ?>
                        <span class="group-chip"
                            style="--group-color: <?= ThemeColor::colorsList()[$group->color->value] ?>">
                            <span class="group-dot"></span>
                            <?= htmlspecialchars($group->name) ?>
                        </span>
                    <?php endforeach ?>
                    <?php if ($hidden): ?>
                        <sl-tooltip content="<?= htmlspecialchars(implode(', ', array_map(fn($g) => $g->name, $hidden))) ?>">
                            <span class="group-chip group-more">+<?= count($hidden) ?></span>
                        </sl-tooltip>
                    <?php endif ?>
                </div>
                <small><?= $message->sentAt->format("d/m H:i") ?></small>
            </article>
        <?php
    }
}
