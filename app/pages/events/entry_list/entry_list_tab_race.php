<?php
$raceId = Component::prop("race_id");
$raceEntries = RaceService::getRaceEntries($raceId);
?>
<?php if (!$raceEntries): ?>
    <p style="padding: 1rem" class="center">Pas d'inscrits sur cette course</p>
    <?php return;
endif ?>
<figure>
    <table>
        <thead>
            <tr>
                <th></th>
                <th>Catégorie</th>
                <th>Remarques</th>
            </tr>
        </thead>
        <tbody>
            <?php
            ob_start();
            $totalEntries = 0;
            foreach ($raceEntries as $entry): ?>
                <?php if ($entry->present):
                    $totalEntries++ ?>
                    <tr class="clickable" tabindex=0 <?= UserModal::props($entry->user->id) ?>>
                        <td>
                            <?= $entry->user->last_name . " " . $entry->user->first_name ?>
                        </td>
                        <td>
                            <?= $entry->category ? $entry->category->name : "Par défaut" ?>
                        </td>
                        <td>
                            <?= $entry->comment ?>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach;
            $table = ob_get_clean() ?>
            <?= TotalRow("Total", $totalEntries, "") ?>
            <?= $table ?>
        </tbody>
    </table>
</figure>