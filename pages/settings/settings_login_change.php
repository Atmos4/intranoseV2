<?php
restrict_access();
require_once "utils/form_validation.php";

$user_id = $_SESSION['user_id'];
$user = em()->find(User::class, $user_id);

$v = validate();
$current_login = $v->text("current_login")->label("Login actuel")->required();
$new_login = $v->text("new_login")->label("Nouveau login")->required()->min_length(3);


if (!empty($_POST) and $v->valid()) {

    $users_with_same_login = em()->getRepository(User::class)->findByLogin($new_login->value);
    if ($user->login != $current_login->value) {
        $current_login->set_error("Mauvais login");
    } elseif (count($users_with_same_login)) {
        $new_login->set_error("Ce login est déjà utilisé");
    } else {
        $user->set_login($new_login->value);
        em()->persist($user);
        em()->flush();
        $v->set_success("Login mis à jour !");
    }
}

page("Changement de login");
?>

<a href="/mon-profil#mon-compte" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
<form method="post">
    <?= $v->render_validation() ?>

    <?= $current_login->render() ?>

    <?= $new_login->render() ?>

    <input type="submit" name="submitLogin" value="Mettre à jour">
</form>