<?php
$activityId = Component::prop("activity_id");
$activityEntries = ActivityService::getActivityEntries($activityId);
?>
<?php if (!$activityEntries): ?>
    <p style="padding: 1rem" class="center">Pas d'inscrits sur cette activité</p>
    <?php return;
endif ?>
<figure class="overflow-auto">
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
            foreach ($activityEntries as $entry): ?>
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