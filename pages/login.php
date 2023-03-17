<?php
$validation_error = "";
if (count($_POST) && (!empty($_POST['login']) || !empty($_POST['password']))) {
    $user = em()->getRepository(User::class)->getByLogin($_POST['login']);

    if (password_verify($_POST['password'], $user->password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_permission'] = $user->permission;
        redirect("/");
    } else
        $validation_error = "Utilisateur non trouvé";
}

page("Login", "login.css", false, false);
?>
<article>
    <form method="post">
        <h2 class="center">Intranose</h2>
        <input type="text" name="login" placeholder="Login" aria-label="Login" autocomplete="off" required>
        <input type="password" name="password" placeholder="Mot de passe" aria-label="Mot de passe"
            autocomplete="current-password" required>
        <?php if ($validation_error): ?>
            <span class="error">
                <?= $validation_error ?>
            </span>
        <?php endif ?>
        <button type="submit">Se connecter</button>
    </form>
</article>