<?php

require_once "database/profil_data.php";

page("Mon profil");
check_auth("USER");

$user_data = get_user_data();
?>


<main class="container">
    <form method="post">
        <!-- Grid -->
        <div class="grid">

            <label for="firstname">
                Prénom
                <input type="text" id="prenom" name="prenom" value=<?= $user_data["prenom"] ?> required>
            </label>

            <label for="nom">
                Nom
                <input type="text" id="nom" name="nom" value=<?= $user_data["nom"] ?> required>
            </label>

        </div>

        <fieldset>
            <legend>Sexe</legend>
            <label for="homme">
                <input type="radio" id="homme" name="sexe" value="H" <?php echo ($user_data["sexe"] == 'H') ? 'checked="checked"' : ''; ?>>
                Homme
            </label>
            <label for="dame">
                <input type="radio" id="dame" name="sexe" value="D" <?php echo ($user_data["sexe"] == 'D') ? 'checked="checked"' : ''; ?>>
                Dame
            </label>
        </fieldset>

        <label for="email">Adresse mail perso</label>
        <input type="email" id="email" name="email" value=<?= $user_data["realmail"] ?> required>

        <button class=col-md-4>Changer le mot de passe</button>

        <label for="emailnose">Adresse mail NOSE</label>
        <input type="email" id="emailnose" name="emailnose" value=<?= $user_data["email"] ?> required>

        <div class="grid">

            <label for="numlicense">
                Numéro de license
                <input type="text" id="numlicense" name="numlicense" value=<?= $user_data["num_lic"] ?> readonly>
            </label>

            <label for="sportident">
                SportIdent
                <input type="text" id="sportident" name="sportident" value=<?= $user_data["sportident"] ?> required>
            </label>

        </div>

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

        <button type="submit" name="submitButton">Enregistrer</button>

    </form>
</main>