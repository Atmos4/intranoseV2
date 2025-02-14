<?php
restrict_access(Access::$EDIT_USERS);
$groups = GroupService::listGroups();
page("Groupes"); ?>
<?= actions()->back("/licencies")->link("/groupes/nouveau", "Nouveau groupe", "fas fa-plus") ?>
<table>
    <?php foreach ($groups as $group): ?>
        <tr class="clickable" tabindex="0" hx-trigger="click,keyup[key=='Enter']" hx-get="/groupes/<?= $group->id ?>"
            hx-target="body" hx-push-url="true">
            <td>
                <?= $group->name ?>
            </td>
            <td class="list-chevron">
                <i class=" fa fa-chevron-right"></i>
            </td>
        </tr>
    <?php endforeach;
    if (!$groups): ?>
        <p class="center">Pas encore de groupes 😲</p>
    <?php endif ?>
</table>