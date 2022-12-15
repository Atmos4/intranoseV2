<?php
restrict_access();

require_once "database/profil_data.php";
//might be changed later for admins
$id = $_SESSION['user_id'];

[$validation_result, $validation_color] = change_profil_data($_POST, $id);
$user_data = get_user_data();
$messageEmail = "";

page("Mon profil");
?>

<main class="container">

    <?php if ($validation_result) : ?>
        <p class=<?= $validation_color ?>><?= $validation_result ?></p>
    <?php endif ?>

    <h2>Identité</h2>

    <div class="grid">
        <label for="firstname">
            Prénom
            <input type="text" id="prenom" name="prenom" value=<?= $user_data["prenom"] ?> disabled>
        </label>

        <label for="nom">
            Nom
            <input type="text" id="nom" name="nom" value=<?= $user_data["nom"] ?> disabled>
        </label>
    </div>

    <div class="grid">
        <label for="numlicense">
            Numéro de license
            <input type="text" id="numlicense" name="numlicense" value=<?= $user_data["num_lic"] ?> disabled>
        </label>

        <fieldset>
            <legend>Sexe</legend>
            <label for="homme">
                <input type="radio" id="homme" name="sexe" value="H" <?php echo ($user_data["sexe"] == 'H') ? 'checked="checked"' : ''; ?> disabled>
                Homme
            </label>
            <label for="dame">
                <input type="radio" id="dame" name="sexe" value="D" <?php echo ($user_data["sexe"] == 'D') ? 'checked="checked"' : ''; ?> disabled>
                Dame
            </label>
        </fieldset>
    </div>

    <hr>

    <h2 id="mon-compte">Compte</h2>

    <form method="post">
        <div class="grid">
            <button type=button class="secondary" onclick="window.location.href = '/mon-profil/changement-mdp'">Changer le mot de passe</button>
            <button type=button class="secondary" onclick="window.location.href = '/mon-profil/changement-login'">Changer le login</button>
        </div>

        <div class="grid">
            <label for="email">
                Adresse mail perso
                <input type="email" id="email" name="email" value=<?= $user_data["realmail"] ?> required>
            </label>

            <label for="emailnose">
                Adresse mail NOSE
                <input type="email" id="emailnose" name="emailnose" value=<?= $user_data["email"] ?> required>
            </label>
        </div>

        <button type="submit" name="submitEMail" class=col-md-4>Mettre à jour les mails</button>
    </form>

    <hr>

    <h2> Infos perso </h2>

    <form method="post">
        <label for="sportident">
            SportIdent
            <input type="text" id="sportident" name="sportident" value=<?= $user_data["sportident"] ?> required>
        </label>

        <label for="adresse">Adresse</label>
        <input type="text" id="adresse" name="adresse" value="<?= $user_data["adresse1"] ?>" required>

        <details>
            <summary>Deuxième ligne d'adresse</summary>
            <p><label for="adresse2">Adresse bis</label>
                <input type="text" id="adresse2" name="adresse2" value="<?php $user_data["adresse2"] ?>" placeholder="L'enfer du postier c'est ici">
            </p>
        </details>

        <div class="grid">
            <label for="codePostal">
                Code postal
                <input type="text" id="codePostal" name="codePostal" value=<?= $user_data["cp"] ?> required>
            </label>

            <label for="ville">
                Ville
                <input type="text" id="ville" name="ville" value=<?= $user_data["ville"] ?> required>
            </label>
        </div>

        <div class="grid">
            <label for="portable">
                Telephone portable
                <input type="text" id="portable" name="portable" value="<?= $user_data["telport"] ?>" required>
            </label>

            <label for="fixe">
                Telephone fixe
                <input type="text" id="fixe" name="fixe" value="<?= $user_data["tel"] ?>" required>
            </label>
        </div>

        <button type="submit" name="submitInfos" class=col-md-4>Mettre à jour les infos</button>
    </form>
</main>