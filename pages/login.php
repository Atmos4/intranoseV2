<?php
require_once "database/login.api.php";
$validation_error = handle_login($_POST);

page("Login", "login.css", false, false);
?>
<article>
    <form method="post">
        <h2 class="center">Intranose</h2>
        <input type="text" name="login" placeholder="Login" aria-label="Login" autocomplete="off" required>
        <input type="password" name="password" placeholder="Mot de passe" aria-label="Mot de passe"
            autocomplete="current-password" required>
        <?php if ($validation_error): ?>
        <span class="error"><?= $validation_error ?></span>
        <?php endif ?>
        <!-- <fieldset>
                    <label for="remember">
                        <input type="checkbox" role="switch" id="remember" name="remember">
                        Se souvenir de moi
                    </label>
                </fieldset> -->
        <button type="submit">Se connecter</button>
    </form>
</article>