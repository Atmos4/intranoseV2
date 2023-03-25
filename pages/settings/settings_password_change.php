<?php
restrict_access();

$user_id = $_SESSION['user_id'];
$user = em()->find(User::class, $user_id);

$v = new Validator();
$current_pass = $v->password("current_password")->label("Mot de passe actuel")->required()->autocomplete("current-password");
$new_pass = $v->password("new_password")->autocomplete("new-password")->label("Nouveau mot de passe")->required()->secure();

$current_pass->condition(password_verify($current_pass->value, $user->password), "Mauvais mot de passe");

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
    <input type="hidden" autocomplete="username" name="username" value="<?= $user->login ?>">
    <?= $v->render_validation() ?>
    <?= $current_pass->render() ?>
    <?= $new_pass->render() ?>
    <input type="submit" name="submitPassword" value="Mettre à jour">
</form>