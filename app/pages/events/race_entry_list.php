<?php
restrict_access(Access::$ADD_EVENTS);

$race_id = get_route_param("race_id");
$race = em()
    ->createQuery('SELECT r,e,re,u FROM Race r JOIN r.event e LEFT JOIN r.entries re LEFT JOIN re.user u WHERE r.id = :raceId')
    ->setParameters(['raceId' => $race_id])
    ->getSingleResult();
$event = $race->event;

page($race->name . " : Inscrits")->css("event_view.css");
?>
<nav id="page-actions">
    <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <?php if ($event->open): ?>
        <button onclick="selectTable()">Selectionner le tableau</button>
    <?php endif ?>
</nav>
<?php if (!$event->open): ?>
    <p class="center">
        <?php "L'évenement de cette course n'est pas encore ouvert 🙃" ?>
    </p>
<?php else: ?>
    <figure>

        <table>
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Prénom</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Surclassé</th>
                    <th scope="col">Remarques</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </figure>
    <script src="/assets/js/select-table.js"></script>
<?php endif ?>