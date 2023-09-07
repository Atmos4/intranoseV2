<?php
restrict_dev();

$name = get_route_param("name", numeric: false);

$ovh = ovh_api();
$details = $ovh->getMailingList($name);
$subscribers = $ovh->getMailingListSubscribers($name);

page("Mailing list") ?>
<details>
    <summary>Details</summary>
    <pre><?= print_r($details) ?></pre>
</details>
<details>
    <summary>Subscribers</summary>
    <ul>
        <?php foreach ($subscribers as $sub): ?>
            <li>
                <?= $sub ?>
            </li>
        <?php endforeach ?>
    </ul>
</details>