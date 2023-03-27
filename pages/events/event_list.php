<?php
restrict_access();
$can_edit = check_auth(
    Permission::COACH,
    Permission::STAFF,
    Permission::ROOT,
    Permission::COACHSTAFF
);

formatter("d MMM");
require_once "database/events.api.php";
$user_id = $_SESSION["user_id"];
$events = Event::listAllOpen($user_id);
if ($can_edit)
    $draft_events = Event::listDrafts();

page("Événements", "event_list.css");

function render_event(EventDto $event)
{
    $diff = date_create('now')->diff($event->deadline->add(new DateInterval("PT23H59M59S")));
    $td_class = $tooltip_content = "";
    if ($diff->invert) {
        $td_class = "passed";
    } elseif ($diff->days < 7) {
        $td_class = "warning";
        $tooltip_content = "data-tooltip=\""
            . ($diff->days == 0 ?
                $diff->format("Plus que %h heures!") :
                $diff->format("Dans %d jour" . ($diff->days == 1 ? "" : "s")))
            . "\"";
    } ?>

    <tr class="event-row clickable" onclick="window.location.href = '/evenements/<?= $event->id ?>'">
        <td class="event-entry">

            <?php if ($event->open):
                if ($event->registered == true): ?>
                    <ins><i class="fas fa-check"></i></ins>
                <?php elseif ($event->registered === false): ?>
                    <del><i class="fas fa-xmark"></i></del>
                <?php else: ?>
                    <i class="fas fa-question"></i>
                <?php endif; else: ?>
                <i class="fas fa-file"></i>
            <?php endif; ?>

        </td>
        <td class="event-name"><b>
                <?= $event->name ?>
            </b></td>
        <td class="event-date">
            <span>
                <?= format_date($event->start) ?>
            </span><i class="fas fa-arrow-right"></i><span>
                <?= format_date($event->end) ?>
            </span>
        </td>
        <td class="event-limit <?= $td_class ?>">
            <i class="fas fa-clock"></i><span <?= $tooltip_content ?>>
                <?= format_date($event->deadline) ?>
            </span>
        </td>
    </tr>

<?php } ?>

<?php if ($can_edit): ?>
    <p class="center">
        <a role="button" href="/evenements/nouveau" class="secondary"><i class="fas fa-plus"></i> Ajouter un événement</a>
    </p>
<?php endif ?>

<table role="grid">
    <?php if (count($events) || ($can_edit && count($draft_events))): ?>
        <thead class=header-responsive>
            <tr>
                <th></th>
                <th>Nom</th>
                <th colspan=2>Dates</th>
            </tr>
        </thead>
        <tbody>

            <?php
            // Draft events
            if ($can_edit && count($draft_events)): ?>
                <tr class="delimiter">
                    <td colspan="4">Événements en attentes</td>
                </tr>
                <?php
                foreach ($draft_events as $draft_event) {
                    render_event($draft_event);
                } ?>

                <tr class="delimiter">
                    <td colspan="4">Événements publiés</td>
                </tr>
            <?php endif ?>

            <?php foreach ($events as $event) {
                render_event($event);
            } ?>

        </tbody>
    <?php else: ?>
        <p class="center">Pas d'événement pour le moment 😴</p>
    <?php endif ?>
</table>