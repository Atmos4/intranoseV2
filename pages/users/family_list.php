<?php
restrict_access(Access::$EDIT_USERS);
$families = em()->getRepository(Family::class)->findAll();
page("Familles") ?>
<nav id="page-actions">
    <a href="/licencies" class="secondary">
        < Retour</a>
</nav>
<table>
    <?php foreach ($families as $family): ?>
        <tr class="clickable" onclick="window.location.href = '/famille/<?= $family->id ?>'">
            <td>
                <?= $family->name ?>
        </tr>
    <?php endforeach;
    if (!$families): ?>
        <p class="center">Pas encore de familles 🥺</p>
    <?php endif ?>