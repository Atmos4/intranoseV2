<?php
restrict_access();
require_once "utils/form_validation.php";

$user_id = $_SESSION['user_id'];
$user = em()->find(User::class, $user_id);

$v = validate();
$current_pass = $v->password("current-password")->label("Mot de passe actuel")->required()->autocomplete("current-password");
$new_pass = $v->password("new-password")->autocomplete("new-password")->label("Nouveau mot de passe")->required()->secure();
$confirm_pass = $v->password("confirm-password")->autocomplete("new-passord")->label("Confirmation")->required();

$current_pass->condition(password_verify($current_pass->value, $user->password), "Mauvais mot de passe");
$confirm_pass->condition($new_pass->value == $confirm_pass->value, "Les deux mots de passes ne correspondent pas");

if (!empty($_POST) and $v->valid()) {
    $user->set_password($new_pass->value);
    em()->persist($user);
    em()->flush();
    $v->set_success("Mot de passe mis à jour !");
}


page("Changement de mot de passe");
?>
<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<form method="post">

    <?= $v->render_validation() ?>

    <?= $current_pass->render() ?>

    <?= $new_pass->render() ?>

    <?= $confirm_pass->render() ?>

    <input type="submit" name="submitPassword" value="Mettre à jour">
</form>