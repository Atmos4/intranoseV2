<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$event = em()->find(Event::class, $event_id);

$all_event_entries = Event::getAllEntries($event_id);

page($event->name . " : Participants")->css("event_view.css");
?>
<nav id="page-actions">
    <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <?php if ($event->open): ?>
        <button onclick="selectTable()">Selectionner le tableau</button>
    <?php endif ?>
</nav>
<?php if (!$event->open): ?>
    <p class="center">Cet évenement n'est pas encore ouvert 🙃</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th scope="col">Transport</th>
                <th scope="col">Hébergement</th>
                <th scope="col">Courses</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_event_entries as $entry): ?>
                <?php if ($entry->present): ?>
                    <tr class="clickable" onclick="window.location.href = '/licencies/<?= $entry->user->id ?>'">
                        <td class="lastname">
                            <?= $entry->user->last_name ?>
                        </td>
                        <td class="firstname">
                            <?= $entry->user->first_name ?>
                        </td>
                        <td class="center">
                            <?= $entry->transport ? "1" : "0" ?>
                        </td>
                        <td class="center">
                            <?= $entry->accomodation ? "1" : "0" ?>
                        </td>
                        <td>
                            <?php
                            $races = [];
                            foreach ($entry->user->race_entries as $re) {
                                array_push($races, $re->race->name);
                            }
                            ?>
                            <?= implode(", ", $races) ?>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach ?>
        </tbody>
    </table>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>