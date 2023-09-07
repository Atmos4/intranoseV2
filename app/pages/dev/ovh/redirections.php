<?php
restrict_dev();

$redirections = ovh_api()->getRedirection();
page("Redirections");

?>
<ul>
    <?php foreach ($redirections as $r): ?>
        <li>
            <?= $r ?>
        </li>
    <?php endforeach ?>
</ul>