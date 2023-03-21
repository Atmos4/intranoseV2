<?php
restrict_access();
require_once "utils/form_validation.php";

$user_id = $_SESSION['user_id'];
$user = em()->find(User::class, $user_id);

$v = validate();
$current_pass = $v->password("current_pass")->label("Mot de passe actuel")->required();
$new_pass = $v->password("new_pass")->set_new()->label("Nouveau mot de passe")->required()->secure();
$confirm_pass = $v->password("confirm_pass")->set_new()->label("Confirmation")->required();

$check_confirm = ($new_pass->value == $confirm_pass->value);

if (!empty($_POST) and $v->valid()) {
    if ($check_confirm) {
        if (password_verify($current_pass->value, $user->password)) {
            $user->set_password($new_pass->value);
            em()->persist($user);
            em()->flush();
            $v->set_success("Mot de passe mis à jour !");
        } else {
            $current_pass->set_error("Mauvais mot de passe");
        }
    } else {
        $confirm_pass->set_error("Les deux mots de passes ne correspondent pas");
    }
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