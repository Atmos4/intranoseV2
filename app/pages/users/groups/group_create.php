<?php
restrict_access(Access::$EDIT_USERS);
page("Créer un groupe");
?>
<?= actions()->back("/groupes") ?>
<?php import(__DIR__ . '/group_edit.php'); ?>