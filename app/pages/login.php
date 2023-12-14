<?php
$v = new Validator();
$login = $v->text("login")->placeholder("Login")->required();
$password = $v->password("password")->placeholder("Password")->autocomplete("current-password")->required();
if ($v->valid()) {
    AuthService::create()->tryLogin($login->value, $password->value, $v);
}

page("Login")->css("login.css")->disableNav()->heading(false);
?>
<article>
    <form method="post" hx-boost="false">
        <h2 class="center">Intranose</h2>
        <div class="grid">
            <?= $login->render() ?>
            <ins tabindex="0" role=link class="help"
                data-tooltip="Même login que sur l'ancien site : &#xa; dupont_a pour André Dupont"
                data-placement="left"><i class="fas fa-circle-info"></i></a>
        </div>
        <?= $password->render() ?>
        <?= $v->render_validation() ?>
        <button type="submit">Se connecter</button>
    </form>
    <a href="/reinitialiser-mot-de-passe">Mot de passe oublié ?</a>
</article>