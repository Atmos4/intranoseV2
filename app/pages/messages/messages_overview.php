<?php
restrict_access();
restrict_feature(Feature::Messages);
$user_id = User::getMainUserId();
$conversations = Conversation::getAllFromUser($user_id);
page("Messages")->css("messages.css") ?>
<ul>
    <?php if (!$conversations): ?>
        <p>
            Pas de messages pour le moment
        </p>
    <?php endif;
    foreach ($conversations as $c):
        if ($c->private_hash):
            $targetUser = $c->participants[0]->directUser ?>
            <li>
                <a href="/messages/direct/<?= Conversation::getUrlFromHash($c->private_hash, $user_id) ?>">
                    <?= "$targetUser->first_name $targetUser->last_name" ?>
                </a>
            </li>
        <?php else: ?>
            <li>Not supported yet</li>
        <?php endif;
    endforeach ?>
</ul>