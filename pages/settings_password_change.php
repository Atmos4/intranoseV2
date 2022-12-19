<?php
restrict_access();
require_once "database/settings.api.php";
[$validation_result, $validation_color] = change_password($_POST, $_SESSION['user_id']);
$user_data = get_user_data();

page("Changement de mot de passe");
?>
<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<form method="post">
    <?php if ($validation_result) : ?>
        <p class=<?= $validation_color ?>><?= $validation_result ?></p>
    <?php endif ?>

    <label for="currentPassword">
        Mot de passe actuel
        <input type="password" id="currentPassword" name="currentPassword" required>
    </label>

    <label for="pass">
        Nouveau mot de passe
        <input type="password" id="password" name="password" required>
    </label>

    <label for="passwordConfirm">
        Confirmation
        <input type="password" id="passwordConfirm" name="passwordConfirm">
    </label>

    <input type="submit" name="submitPassword" value="Mettre à jour">
</form>