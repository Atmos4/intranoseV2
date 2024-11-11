<?php
restrict_dev();

function DevButton($href, $label)
{
    return <<<EOL
    <li><a href="$href" role="button" class="contrast outline">$label</a></li>
    EOL;
}

page("Dev") ?>
<b>User control</b>
<nav>
    <ul>
        <?= DevButton("/dev/create-user", "Créer utilisateur") ?>
        <?= DevButton("/dev/change-access", "Change access") ?>
    </ul>
</nav>
<b>APIs</b>
<nav>
    <ul>
        <?= DevButton("/dev/send-email", "Email") ?>
    </ul>
</nav>
<b>Tests</b>
<nav>
    <ul>
        <?= DevButton("/dev/toast", "Toasts") ?>
        <?= DevButton("/dev/random", "Random") ?>
    </ul>
</nav>
<b>Notifications</b>
<nav>
    <ul>
        <?= DevButton("/dev/notifications", "SW et Notifications") ?>
    </ul>
</nav>