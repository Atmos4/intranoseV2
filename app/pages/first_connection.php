<?php
$v = new Validator();
$login = $v->text("login")->placeholder("Login")->required();

if ($v->valid()) {
    AuthService::create()->tryOnlyLogin($login->value, $v);
}

page("Réinitialisation du mot de passe")->css("login.css")->disableNav()->heading(false);
?>

<?= actions()->back("/login") ?>
<article>
    <form method="post" hx-boost="false">
        <?= $login->render() ?>
        <?= $v->render_validation() ?>
        <button type="submit">Activer le compte</button>
        <p>
            Votre login vous a été envoyé par un membre du club. Si vous n'avez rien reçu, voyez directement avec
            l'administrateur.
        </p>
    </form>
</article>