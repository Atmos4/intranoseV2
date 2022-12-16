<?php
restrict_access();

require_once "database/events_data.php";
$events = get_events();
$user_id = $_SESSION["user_id"];

page("Mes inscriptions", "event_list.css");
?>

<table role="grid">
    <thead class=header-responsive>
        <tr>
            <th></th>
            <th>Nom</th>
            <th colspan=2>Dates</th>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($events as $event) : ?>
            <tr class="event-row" onclick="window.location.href = '/evenements/<?= $event['did'] ?>'">
                <td class="event-entry">
                    <?php if (is_registered($event, $user_id)) : ?>
                        <ins><i class="fas fa-check"></i></ins>
                    <?php else : ?>
                        <del><i class="fas fa-xmark"></i></del>
                    <?php endif ?>
                </td>
                <td class="event-name"><b><?= $event['nom'] ?></b></td>
                <td class="event-date">
                    <span><?= $event['depart'] ?></span><i class="fas fa-arrow-right"></i><span><?= $event['arrivee'] ?></span>
                </td>
                <td class="event-limit">
                    <i class="fas fa-clock"></i><span><?= $event['limite'] ?></span>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>