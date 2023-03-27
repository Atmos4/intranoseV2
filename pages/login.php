<?php
$v = new Validator();
$login = $v->text("login")->placeholder("Login")->required();
$password = $v->password("password")->placeholder("Password")->autocomplete("current-password")->required();
if ($v->valid()) {
    $user = User::getByLogin($login->value);
    if (password_verify($password->value, $user->password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_permission'] = $user->permission;
        redirect("/");
    } else {
        $login->set_error("Utilisateur non trouvé");
    }
}

page("Login", "login.css", false, false);
?>
<article>
    <form method="post">
        <h2 class="center">Intranose</h2>
        <?= $login->render() ?>
        <?= $password->render() ?>
        <?= $v->render_validation() ?>
        <button type="submit">Se connecter</button>
    </form>
</article>