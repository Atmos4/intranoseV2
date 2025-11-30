<?php
$groups = GroupService::listGroups();
page("Groupes"); ?>
<?= actions()->back("/licencies")->link("/groupes/nouveau", "Nouveau groupe", "fas fa-plus") ?>
<table>
    <?php foreach ($groups as $group): ?>
        <details>
            <summary>
                <?= $group->name ?>
            </summary>
            <div class="buttons-grid">
                <a role="button" class="outline secondary" href='/groupes/<?= $group->id ?>'>
                    <i class="fa fa-circle-info"></i>
                    Détails</a>
            </div>
        </details>
        <hr>
    <?php endforeach;
    if (!$groups): ?>
        <p class="center">Pas encore de groupes 😲</p>
    <?php endif ?>
</table>