<?php
restrict_access();

require_once "database/settings.api.php";
[$validation_result, $validation_color] = change_login($_POST, $_SESSION['user_id']);

page("Changement de login");
?>

<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<?php if ($validation_result) : ?>
    <p class=<?= $validation_color ?>><?= $validation_result ?></p>
<?php endif ?>
<form method="post">
    <label for="login">
        Login actuel
        <input type="text" id="login" name="login" required>
    </label>

    <label for="newLogin">
        Nouveau login
        <input type="text" id="newLogin" name="newLogin">
    </label>

    <input type="submit" name="submitLogin" value="Mettre à jour">
</form>