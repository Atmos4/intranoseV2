<?php
restrict_access();
require_once "database/settings.api.php";
require_once "utils/form_validation.php";

$user_id = $_SESSION['user_id'];
$user = em()->find(User::class, $user_id);

$v = validate();
$current_login = $v->text("current_login")->label("Login actuel")->required();
$new_login = $v->text("new_login")->label("Nouveau login")->required()->min_length(3);

if (!empty($_POST) and $v->valid()) {
    if ($user->login == $current_login->value) {
        $user->set_login($new_login->value);
        em()->persist($user);
        em()->flush();
        $v->set_success("Login mis à jour !");
    } else {
        $current_login->set_error("Mauvais login");
    }
}

page("Changement de login");
?>

<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<form method="post">
    <?= $v->render_errors() ?>
    <?= $v->render_success() ?>

    <?= $current_login->render() ?>

    <?= $new_login->render() ?>

    <input type="submit" name="submitLogin" value="Mettre à jour">
</form>