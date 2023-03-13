<?php
restrict_access();

formatter("d MMM");
require_once "database/events.api.php";
$user_id = $_SESSION["user_id"];
$events = get_events($user_id);
$draft_events = get_draft_events();

page("Événements", "event_list.css");

function render_event($event)
{
    $diff = date_create('now')->diff(date_create($event['limite'])->add(new DateInterval("PT23H59M59S")));
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

    <tr class="event-row clickable" onclick="window.location.href = '/evenements/<?= $event['did'] ?>'">
        <td class="event-entry">

            <?php if (array_key_exists('present', $event)):
                if ($event["present"]): ?>
                    <ins><i class="fas fa-check"></i></ins>
                <?php else: ?>
                    <del><i class="fas fa-xmark"></i></del>
                <?php endif; else: ?>
                <i class="fas fa-file"></i>
            <?php endif; ?>

        </td>
        <td class="event-name"><b>
                <?= $event['nom'] ?>
            </b></td>
        <td class="event-date">
            <span>
                <?= format_date($event['depart']) ?>
            </span><i class="fas fa-arrow-right"></i><span>
                <?= format_date($event['arrivee']) ?>
            </span>
        </td>
        <td class="event-limit <?= $td_class ?>">
            <i class="fas fa-clock"></i><span <?= $tooltip_content ?>>
                <?= format_date($event['limite']) ?>
            </span>
        </td>
    </tr>

<?php } ?>

<?php if (check_auth("COACH", "STAFF", "ROOT", "COACHSTAFF")): ?>
    <p class="center">
        <a role="button" href="/evenements/nouveau" class="secondary"><i class="fas fa-plus"></i> Ajouter un événement</a>
    </p>
<?php endif ?>

<table role="grid">
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
        if (count($draft_events)): ?>
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
</table>