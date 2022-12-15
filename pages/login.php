<?php
require_once "database/login_data.php";
$validation_error = handle_login($_POST);

page("Login", "login.css", false);
?>

<main class="container small">
    <article class="grid">
        <div>
            <h1>Intranose</h1>
            <form method="post">
                <input type="text" name="login" placeholder="Login" aria-label="Login" autocomplete="off" required>
                <input type="password" name="password" placeholder="Mot de passe" aria-label="Mot de passe" autocomplete="current-password" required>
                <?php if ($validation_error) : ?>
                    <del><?= $validation_error ?></del>
                <?php endif ?>
                <!-- <fieldset>
                    <label for="remember">
                        <input type="checkbox" role="switch" id="remember" name="remember">
                        Se souvenir de moi
                    </label>
                </fieldset> -->
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </article>
</main>