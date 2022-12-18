<?php
restrict_access();

require_once "database/events.api.php";
$event = get_event_by_id(get_route_param('id_depl', true), $_SESSION['user_id']);
$competitions = get_competitions_by_event_id($event['did'], $_SESSION['user_id']);

$has_entry = isset($event['present']) && $event['present'] == 1;
$is_transported = isset($event['transport']) && $event['transport'] == 1;
$is_hosted = isset($event['heberg']) && $event['heberg'] == 1;

page($event['nom'], "event_view.css");
?>
<div class="page-actions">
    <a href="/evenements" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <a href="/evenements/<?= $event['did'] ?>/inscription" class="secondary">
        <i class="fas fa-pen"></i> Modifier
    </a>
</div>
<article>
    <header class="center">
        <div class="row">
            <div class="col-sm-6">
                <?php include "components/start_icon.php" ?>

                <span><?= "Départ : " . format_date($event['depart']) ?></span>
            </div>
            <div class="col-sm-6">
                <?php include "components/finish_icon.php" ?>
                <span><?= "Retour : " . format_date($event['arrivee']) ?></span>
            </div>
            <div>
                <i class="fas fa-clock"></i>
                <span><?= "Date limite : " . format_date($event['limite']) ?></span>
            </div>
        </div>

        <div>
            <b>
                <?php if (isset($event['present']) && $event['present'] == 1) : ?>
                    <ins><i class="fas fa-check"></i>
                        <span>Je participe</span></ins>
                <?php else : ?>
                    <del><i class="fas fa-xmark"></i>
                        <span><?= isset($event['present']) ? "Je ne participe pas" : "Pas inscrit" ?></span></del>
                <?php endif;  ?>
            </b>
        </div>
    </header>
    <div class="grid">
        <?php if ($has_entry) : ?>
            <p>
                <?php if ($is_transported) : ?>
                    <ins><i class="fas fa-check"></i></ins>
                <?php else : ?>
                    <del><i class="fas fa-xmark"></i></del>
                <?php endif;  ?>
                <span>Transport avec le club</span>
            </p>
            <p>
                <?php if ($is_hosted) : ?>
                    <ins><i class="fas fa-check"></i></ins>
                <?php else : ?>
                    <del><i class="fas fa-xmark"></i></del>
                <?php endif; ?>
                <span>Hébergement avec le club</span>
            </p>
        <?php endif; ?>
    </div>
    <h4>Courses : </h4>
    <table role="grid">
        <?php foreach ($competitions as $competition) : ?>
            <tr class="display">
                <td class="competition-entry">
                    <?php if (isset($competition['present']) && $competition['present'] == 1) : ?>
                        <ins><i class="fas fa-check"></i></ins>
                    <?php else : ?>
                        <del><i class="fas fa-xmark"></i></del>
                    <?php endif;  ?>
                </td>
                <td class="competition-name"><b><?= $competition['nom'] ?></b></td>
                <td class="competition-date"><?= format_date($competition['date']) ?></td>
                <td class="competition-place"><?= $competition['lieu'] ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</article>
