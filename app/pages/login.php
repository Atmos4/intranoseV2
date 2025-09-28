<?php
$v = new Validator(["remember_me" => true]);
$login = $v->text("login")->placeholder("Login ou email")->required();
$password = $v->password("password")->placeholder("Password")->autocomplete("current-password")->required();
$rememberMe = $v->switch("remember_me")->label("Rester connecté");
if ($v->valid()) {
    AuthService::create()->tryLogin($login->value, $password->value, $rememberMe->value, $v) && redirect("/");
}

page("Login")->css("login.css")->disableNav()->heading(false);
?>
<article>
    <?php if (ClubManagementService::isClubSelectionAvailable()): ?>
        <a href="/logout-club">Change club</a>
    <?php endif ?>
    <form method="post" hx-boost="false">
        <a href="/about" class="center login-logo contrast">
            <?= import(__DIR__ . "/../components/linklub_logo.php")(!env("INTRANOSE")) ?>
        </a>
        <?= $login->render() ?>
        <?= $password->render() ?>
        <?= $rememberMe->render() ?>
        <?= $v->render_validation() ?>
        <button type="submit">Se connecter</button>
    </form>
    <a href="/reinitialiser-mot-de-passe">Mot de passe oublié ?</a>
</article>
