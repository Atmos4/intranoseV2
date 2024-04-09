<?php
$v = new Validator();
$login = $v->text("login")->placeholder("Login")->required();
$password = $v->password("password")->placeholder("Password")->autocomplete("current-password")->required();
$rememberMe = $v->switch("remember_me")->label("Rester connecté");
if ($v->valid()) {
    AuthService::create()->tryLogin($login->value, $password->value, $rememberMe->value, $v);
}

page("Login")->css("login.css")->disableNav()->heading(false);
?>
<article>
    <form method="post" hx-boost="false">
        <h2 class="center">Intranose</h2>
        <?= $login->render() ?>
        <?= $password->render() ?>
        <?= $rememberMe->render() ?>
        <?= $v->render_validation() ?>
        <button type="submit">Se connecter</button>
    </form>
    <a href="/reinitialiser-mot-de-passe">Mot de passe oublié ?</a>
</article>