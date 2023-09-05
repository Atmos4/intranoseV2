<?php
$token = AccessToken::retrieve($_GET['token'] ?? "");
$v = new Validator(["username" => $token->user->login], action: "reset_password_form");
$username = $v->text("username")->autocomplete("username")->label("Login")->readonly();
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
    <form method="post">
        <?= $v->render_validation() ?>
        <?= $username->render() ?>
        <?= $new_password->render() ?>
        <?= $confirm_password->render() ?>
        <input type="submit" class="outline" value="Enregistrer">
    </form>
</article>