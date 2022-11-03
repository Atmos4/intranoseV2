<?php
$title = "Login";
$css = "assets/css/login.css";
ob_start();
?>

<main class="container small">
    <article class="grid">
        <div>
            <h1>Intranose</h1>
            <form>
                <input type="text" name="login" placeholder="Login" aria-label="Login" autocomplete="off" required>
                <input type="password" name="password" placeholder="Mot de passe" aria-label="Mot de passe" autocomplete="current-password" required>
                <fieldset>
                    <label for="remember">
                        <input type="checkbox" role="switch" id="remember" name="remember">
                        Se souvenir de moi
                    </label>
                </fieldset>
                <button type="submit" onclick="event.preventDefault()">Se connecter</button>
            </form>
        </div>
    </article>
</main><!-- ./ Main -->

<?php
$content = ob_get_clean();
require "template/layout.php";
