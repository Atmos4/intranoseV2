<?php
restrict_access(Access::$ADD_EVENTS);

$race_id = get_route_param("race_id", false);
if ($race_id) {
    $race = em()
        ->createQuery('SELECT r,e,re,u FROM Race r JOIN r.event e JOIN r.entries re JOIN re.user u WHERE r.id = :raceId')
        ->setParameters(['raceId' => $race_id])
        ->getSingleResult();
    $event = $race->event;
} else {
    $event_id = get_route_param("event_id");
    $event = em()->find(Event::class, $event_id);
    $all_event_entries = Event::getAllEntries($event_id);
}

page(($race_id ? $race->name : $event->name) . " : Inscrits")->css("event_view.css");
?>
<nav id="page-actions">
    <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <?php if ($event->open): ?>
        <button onclick="selectTable()">Selectionner le tableau</button>
    <?php endif ?>
</nav>
<?php if (!$event->open): ?>
    <p class="center">
        <?php $race_id ? "L'évenement de cette course n'est pas encore ouvert 🙃" : "Cet évenement n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <figure>

        <table>
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Prénom</th>
                    <?php if ($race_id): ?>
                        <th scope="col">Catégorie</th>
                        <th scope="col">Surclassé</th>
                        <th scope="col">Remarques</th>
                    <?php else: ?>
                        <th scope="col">Transport</th>
                        <th scope="col">Hébergement</th>
                        <th scope="col">Courses</th>
                    <?php endif ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($race_id): ?>
                    <?php foreach ($race->entries as $entry): ?>
                        <?php if ($entry->present): ?>
                            <tr class="clickable" onclick="window.location.href = '/licencies/<?= $entry->user->id ?>'">
                                <td class="lastname">
                                    <?= $entry->user->last_name ?>
                                </td>
                                <td class="firstname">
                                    <?= $entry->user->first_name ?>
                                </td>
                                <td class="center">
                                    <?= $entry->category ? $entry->category->name : "" ?>
                                </td>
                                <td class="center">
                                    <?= $entry->upgraded ? "Oui" : "Non" ?>
                                </td>
                                <td>
                                    <?= $entry->comment ?>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach ?>
                <?php else: ?>
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
                <?php endif ?>
            </tbody>
        </table>
    </figure>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>