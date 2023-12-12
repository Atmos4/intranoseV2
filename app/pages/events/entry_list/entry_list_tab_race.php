<?php
$race_id = Component::prop("race_id");
$race = em()
    ->createQuery('SELECT r,e,re,u FROM Race r JOIN r.event e LEFT JOIN r.entries re LEFT JOIN re.user u WHERE r.id = :raceId')
    ->setParameters(['raceId' => $race_id])
    ->getSingleResult();
?>
<figure>
    <table>
        <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th scope="col">Catégorie</th>
                <th scope="col">Remarques</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($race->entries as $entry): ?>
                <?php if ($entry->present): ?>
                    <tr class="clickable" tabindex=0 <?= UserModal::props($entry->user->id) ?>>
                        <td>
                            <?= $entry->user->last_name ?>
                        </td>
                        <td>
                            <?= $entry->user->first_name ?>
                        </td>
                        <td>
                            <?= $entry->category ? $entry->category->name : "" ?>
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