<?php

require_once "database/profil_data.php";

page("Changement de mot de passe");
check_auth("USER");

[$validation_result, $validation_color] = modify_password($_POST);
$user_data = get_user_data();
?>

<main class="container col-md-6 col-lg-6">
    <form method="post">

        <a href="/mon-profil" class="return-link">Retour au profil</a>

        <h2>Changement de mot de passe</h2>

        <?php if ($validation_result) : ?>
            <p class=<?= $validation_color ?>><?= $validation_result ?></p>
        <?php endif ?>

        <div>
            <label for="currentPassword">Mot de passe actuel</label>
            <input type="password" id="currentPassword" name="currentPassword" required>
        </div>

        <div>
            <label for="pass">Nouveau mot de passe :</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label for="passwordConfirm">Confirmation :</label>
            <input type="password" id="passwordConfirm" name="passwordConfirm">
        </div>

        <input type="submit" name="submitPassword" value="Mettre à jour">

    </form>
</main>