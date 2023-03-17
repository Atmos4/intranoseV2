<?php
restrict_access();
$can_edit = check_auth(
    Permission::COACH,
    Permission::STAFF,
    Permission::ROOT,
    Permission::COACHSTAFF
);

require_once "database/events.api.php";
require_once "components/conditional_icon.php";

$event = get_event_data(get_route_param('event_id'), $_SESSION['user_id']);

$entry = $event->entries[0] ?? null;
$has_file = false; //$event-> != 0;

page($event->name, "event_view.css");
?>
<div id="page-actions">
    <a href="/evenements" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

    <?php if ($event->open && $event->deadline >= date_create("Y-m-d")): ?>
        <a href="/evenements/<?= $event->id ?>/inscription">
            <i class="fas fa-pen-to-square"></i> Inscription
        </a>
    <?php elseif (!$event->open): ?>
        <a href="/evenements/<?= $event->id ?>/publier">
            <i class="fas fa-paper-plane"></i> Publier
        </a>
    <?php endif ?>

    <?php if ($can_edit): ?>
        <a href="/evenements/<?= $event->id ?>/modifier" class="secondary">
            <i class="fas fa-pen"></i> Modifier
        </a>
    <?php endif ?>
</div>
<article>
    <header class="center">
        <div class="row">
            <div>
                <?php include "components/start_icon.php" ?>
                <span>
                    <?= "Départ : " . format_date($event->start_date) ?>
                </span>
            </div>
            <div>
                <?php include "components/finish_icon.php" ?>
                <span>
                    <?= "Retour : " . format_date($event->end_date) ?>
                </span>
            </div>
            <div>
                <i class="fas fa-clock"></i>
                <span>
                    <?= "Date limite : " . format_date($event->deadline) ?>
                </span>
            </div>
        </div>

        <?php /* if ($has_file): ?>
         <div class="file-button">
         <a href="/download?id=<?= $event["circu"] ?>" role="button">
         <div>
         <i class="fas fa-paperclip"></i>
         </div>
         <div>
         <b>Informations</b>
         </div>
         </a>
         </div>
         <?php endif */?>

        <div>
            <b>
                <?php if ($entry && $entry->present): ?>
                    <ins><i class="fas fa-check"></i>
                        <span>Je participe</span></ins>
                <?php else: ?>
                    <del><i class="fas fa-xmark"></i>
                        <span>
                            <?= $entry ? "Je ne participe pas" : "Pas inscrit" ?>
                        </span></del>
                <?php endif; ?>
            </b>
        </div>
    </header>
    <?php if ($entry): ?>
        <div class="grid">
            <p>
                <?= ConditionalIcon($entry->transport, "Transport avec le club") ?>
            </p>
            <p>
                <?= ConditionalIcon($entry->accomodation, "Hébergement avec le club") ?>
            </p>
        </div>

        <?php if ($entry->comment): ?>
            <label><small><i>Remarques:</i></small></label>
            <p>
                <?= $entry->comment ?>
            </p>
        <?php endif;
    endif; ?>

    <?php if (count($event->races)): ?>
        <h4>Courses : </h4>
        <table role="grid">

            <?php foreach ($event->races as $race):
                $race_entry = $race->entries[0] ?? null; ?>
                <tr class="display <?= $can_edit ? "clickable" : "" ?>" <?= $can_edit ? "onclick=\"window.location.href = '/evenements/{$event->id}/course/{$race->id}'\"" : "" ?>>
                    <td class="competition-entry">
                        <?= ConditionalIcon($race_entry && $race_entry->present) ?>
                    </td>
                    <td class="competition-name"><b>
                            <?= $race->name ?>
                        </b></td>
                    <td class="competition-date">
                        <?= format_date($race->date) ?>
                    </td>
                    <td class="competition-place">
                        <?= $race->place ?>
                    </td>
                </tr>
                <?php if ($race_entry && ($race_entry->present || $race_entry->comment)): ?>
                    <tr class="edit">
                        <td colspan="4">
                            <div class="row">
                                <div class="col-auto">
                                    <?= $race_entry->upgraded ? ConditionalIcon($race_entry->upgraded, "Surclassé") : "" ?>
                                </div>
                                <div class="col-auto">
                                    <?= $race_entry->comment ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif;
            endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($can_edit): ?>
        <p>
            <a role=button class="secondary" href="/evenements/<?= $event->id ?>/ajouter-course">
                <i class="fas fa-plus"></i> Ajouter une course</a>
        </p>
    <?php endif; ?>
</article>