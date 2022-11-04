<?php
if (count($_POST) && (!empty($_POST['login']) || !empty($_POST['password']))) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $user_data = fetch(
        "SELECT login, password, perm, nom, prenom, id, valid 
        FROM licencies
        WHERE login=? AND password=? LIMIT 1;",
        [$login, $password]
    );

    var_dump($user_data);
}

$title = "Login";
$css = "assets/css/login.css";
ob_start();
?>

<main class="container small">
    <article class="grid">
        <div>
            <h1>Intranose</h1>
            <form method="post">
                <input type="text" name="login" placeholder="Login" aria-label="Login" autocomplete="off" required>
                <input type="password" name="password" placeholder="Mot de passe" aria-label="Mot de passe" autocomplete="current-password" required>
                <fieldset>
                    <label for="remember">
                        <input type="checkbox" role="switch" id="remember" name="remember">
                        Se souvenir de moi
                    </label>
                </fieldset>
                <button type="submit">Se connecter</button>
            </form>
        </div>
    </article>
</main>

<?php
$content = ob_get_clean();
require "template/layout.php";
