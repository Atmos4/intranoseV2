<?php
restrict_dev();

$ovh = ovh_api();
$domain = 'nose42.fr';

$user = User::getCurrent();

$mailingLists = $ovh->getMailingLists();

page("Mailing list") ?>
<ul>
    <?php foreach ($mailingLists as $list): ?>
        <li>
            <a href="/dev/ovh/mailing-list/<?= $list ?>"><?= $list ?></a>
        </li>
    <?php endforeach ?>
</ul>