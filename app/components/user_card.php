<?php
function UserCard($user, $image = null, $subtitle = null, $actions = null, $user_link = null, $options = [])
{
    ?>
    <article class="user-card">
        <!-- Image block -->
        <?php if ($image): ?>
            <?php $image($user); ?>
        <?php else: ?>
            <img src="<?= $user->getPicture() ?>">
        <?php endif; ?>

        <?php if ($user_link):
            $user_link();
        else: ?>
            <a href="/licencies?user=<?= $user->id ?>" <?= UserModal::props($user->id) ?>>
                <?= "$user->first_name $user->last_name" ?>
            </a>
        <?php endif; ?>

        <!-- subtitle block -->
        <?php $subtitle && $subtitle($user) ?>

        <!-- Actions block -->
        <?php if ($actions): ?>
            <nav>
                <ul>
                    <li>
                        <?php $actions($user); ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </article>
    <?php
} ?>