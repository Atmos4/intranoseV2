<?php
$token = AccessToken::retrieve($_GET['token'] ?? "");
$v = new Validator(action: "reset_password_form");
$new_password = $v->password("new_password")->autocomplete("new-password")->placeholder("Nouveau mot de passe")->required()->secure();
$confirm_password = $v->password("confirm_password")
    ->autocomplete("new-password")
    ->placeholder("Confirmer le mot de passe")
    ->required();
$confirm_password->condition($new_password->value == $confirm_password->value, "Les deux mots de passe sont différents");

if ($v->valid()) {
    $user = $token->user;
    $user->set_password($new_password->value);
    em()->persist($user);
    em()->flush();
    redirect("/");
}
page("Réinitialiser le mot de passe")->disableNav()->heading(false);
?>

<article>
    <h2 class="center">Réinitialisation de mot de passe</h2>
    <p> Utilisateur :
        <?= "{$token->user->first_name} {$token->user->last_name}" ?>
    </p>
    <form method="post" class="row center">
        <?= $v->render_validation() ?>
        <div class="col-sm-12 col-md-8">
            <h2 id="password">Mot de passe</h2>
            <legend>Mot de passe</legend>
            <?= $new_password->render() ?>
            <legend>Confirmation</legend>
            <?= $confirm_password->render() ?>
            <input type="submit" class="outline" value="Enregistrer">
        </div>
    </form>
</article>