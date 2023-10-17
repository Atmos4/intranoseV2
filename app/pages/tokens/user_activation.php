<?php
$token = AccessToken::retrieve($_GET['token'] ?? "");
page("Activer le compte")->disableNav()->heading(false);
$v = new Validator(["username" => $token->user->login], action: "validate_form");
$username = $v->text("username")->autocomplete("username")->label("Votre nom d'utilisateur")->readonly();
$new_password = $v->password("new_password")
    ->autocomplete("new-password")
    ->placeholder("Nouveau mot de passe")
    ->label("Saisissez votre mot de passe")
    ->required()
    ->secure();
$confirm_password = $v->password("confirm_password")
    ->autocomplete("new-password")
    ->placeholder("Confirmer le mot de passe")
    ->required();
$confirm_password->condition($new_password->value == $confirm_password->value, "Les deux mots de passe sont différents");
$gender = $v->text("gender")->label("Sexe");
$phone = $v->phone("phone")->label("Numéro de téléphone");

if ($v->valid()) {
    $user = $token->user;
    $user->set_password($new_password->value);
    $user->gender = Gender::from($gender->value);
    $user->phone = $phone->value;
    $user->status = UserStatus::ACTIVE;
    em()->persist($user);
    em()->remove($token);
    em()->flush();

    // Reset session
    $_SESSION = [];
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_permission'] = $user->permission;

    redirect("/");
}

?>

<article>
    <h2 class="center">Bienvenue au NOSE !</h2>
    <p>
        Bienvenue,
        <?= "{$token->user->first_name} {$token->user->last_name}" ?> ! Remplis ces dernières informations avant de
        pouvoir accéder à ton compte :
    </p>

    <form method="post" class="row">
        <?= $v->render_validation() ?>
        <div class="col-sm-12 col-md-6">
            <?= $username->render() ?>
            <?= $new_password->render() ?>
            <?= $confirm_password->render() ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $phone->render() ?>
            <fieldset>
                <legend>Sexe</legend>
                <label for="man">
                    <input type="radio" id="man" name="gender" value=<?= Gender::M->value ?>>
                    Homme
                </label>
                <label for="woman">
                    <input type="radio" id="woman" name="gender" value=<?= Gender::W->value ?>>
                    Dame
                </label>
            </fieldset>
        </div>
        <div class="col"><input type="submit" class="outline" value="Enregistrer"></div>
    </form>
</article>