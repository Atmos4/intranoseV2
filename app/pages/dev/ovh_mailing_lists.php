<?php
restrict_dev();

$ovh = ovh_api();
$domain = 'nose42.fr';

$mailingLists = $ovh->get("/email/domain/$domain/mailingList");

page("Mailing list") ?>
<ul>
    <?php foreach ($mailingLists as $list): ?>
        <li>
            <?= $list ?>
        </li>
    <?php endforeach ?>
</ul>