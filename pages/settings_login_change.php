<?php

require_once "database/settings_data.php";

page("Changement de login");
check_auth("USER");

$id = $_SESSION['user_id'];

[$validation_result, $validation_color] = change_login($_POST, $id);
?>
<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<h2 class="center">Changement de login</h2>

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