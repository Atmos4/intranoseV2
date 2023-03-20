<?php
restrict_access();
require_once "database/settings.api.php";
require_once "utils/form_validation.php";

$user_id = $_SESSION['user_id'];
[$validation_result, $validation_color] = change_password($_POST, $_SESSION['user_id']);
$user = em()->find(User::class, $user_id);

$v = validate();
$current_pass = $v->password("current_pass")->label("Mot de passe actuel")->required();
$new_pass = $v->password("new_pass")->label("Nouveau mot de passe")->required();
$confirm_pass = $v->password("confirm_pass")->label("Confirmation")->required();

$check_confirm = ($new_pass->value == $confirm_pass->value);

if (!empty($_POST)) {
    if ($v->valid()) {
        if ($check_confirm) {
            if (password_verify($current_pass->value, $user->password)) {
                $user->set_password($new_pass->value);
                em()->persist($event);
                em()->flush();

            } else {
                $current_pass->set_error("Mauvais mot de passe");
            }
        } else {
            $confirm_pass->set_error("Les deux mots de passes ne correspondent pas");
        }
    }
}


page("Changement de mot de passe");
?>
<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<form method="post">

    <?= $v->render_errors() ?>
    <?php if ($validation_result): ?>
        <p class="success">
            <?= $validation_result ?>
        </p>
    <?php endif ?>

    <?= $current_pass->render() ?>

    <?= $new_pass->render() ?>

    <?= $confirm_pass->render() ?>

    <input type="submit" name="submitPassword" value="Mettre à jour">
</form>