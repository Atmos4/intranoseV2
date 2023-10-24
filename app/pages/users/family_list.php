<?php
restrict_access(Access::$EDIT_USERS);
$families = em()->getRepository(Family::class)->findAll();
page("Familles") ?>
<nav id="page-actions">
    <a href="/licencies" class="secondary">
        <i class="fa fa-caret-left"></i> Retour</a>
</nav>
<table>
    <?php foreach ($families as $family): ?>
        <tr class="clickable" tabindex="0" hx-trigger="click,keyup[key=='Enter']" hx-get="/famille/<?= $family->id ?>"
            hx-target="body" hx-push-url="true">
            <td>
                <?= $family->name ?>
            </td>
        </tr>
    <?php endforeach;
    if (!$families): ?>
        <p class="center">Pas encore de familles 🥺</p>
    <?php endif ?>