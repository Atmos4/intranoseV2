<?php
restrict_access(Access::$EDIT_USERS);
$families = em()->getRepository(Family::class)->findAll();
page("Familles") ?>

<?= actions()->back("/licencies") ?>
<table>
    <?php foreach ($families as $family): ?>
        <tr class="clickable" tabindex="0" hx-trigger="click,keyup[key=='Enter']" hx-get="/famille/<?= $family->id ?>"
            hx-target="body" hx-push-url="true">
            <td>
                <?= $family->name ?>
            </td>
            <td class="list-chevron">
                <i class=" fa fa-chevron-right"></i>
            </td>
        </tr>
    <?php endforeach;
    if (!$families): ?>
        <p class="center">Pas encore de familles 🥺</p>
    <?php endif ?>
</table>