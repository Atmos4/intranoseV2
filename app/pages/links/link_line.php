<?php

$link = Component::prop("link");
$disable_delete = Component::prop("disable_delete");

?>
<hr>
<div class="link-grid">
    <div>
        <a href=<?= $link->url ?> target="#blank" role="button"><?= $link->button_text ?></a>
    </div>
    <div><?= $link->description ?></div>
    <?php if (!$disable_delete && check_auth(Access::$EDIT_USERS)): ?>
        <a href="/liens-utiles/supprimer/<?= $link->id ?>"><del><i class="fas fa-trash"></i></del></a>
    <?php endif ?>
</div>