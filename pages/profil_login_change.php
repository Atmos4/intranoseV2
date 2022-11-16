<?php

require_once "database/profil_data.php";

page("Changement de mot de login");
check_auth("USER");

$id = $_SESSION['user_id'];

[$validation_result, $validation_color] = change_login($_POST, $id);
$user_data = get_user_data();
?>

<main class="container col-md-6 col-lg-6">
    <form method="post">

        <a href="/mon-profil" class="return-link">Retour au profil</a>

        <h2>Changement de login</h2>

        <?php if ($validation_result) : ?>
            <p class=<?= $validation_color ?>><?= $validation_result ?></p>
        <?php endif ?>

        <div>
            <label for="login">Login actuel</label>
            <input type="text" id="login" name="login" required>
        </div>

        <div>
            <label for="pass">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label for="newLogin">Nouveau login :</label>
            <input type="text" id="newLogin" name="newLogin">
        </div>

        <input type="submit" name="submitLogin" value="Mettre à jour">

    </form>
</main>