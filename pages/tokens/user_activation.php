<?php
$token = AccessToken::retrieve($_GET['token'] ?? "");
page("Activer le compte");
$v = new Validator(action: "validate_form");
$new_password = $v->password("new_password")->autocomplete("new-password")->placeholder("Nouveau mot de passe")->required()->secure();
$confirm_password = $v->password("confirm_password")
    ->autocomplete("new-password")
    ->placeholder("Confirmer le mot de passe")
    ->required();
$confirm_password->condition($new_password->value == $confirm_password->value, "Les deux mots de passe sont différents");
$gender = $v->text("gender")->label("Sexe");
$phone = $v->phone("phone")->label("Numéro de téléphone")->placeholder();

if ($v->valid()) {
    $user = $token->user;
    $user->set_password($new_password->value);
    $user->gender = Gender::from($gender->value);
    $user->phone = $phone->value;
    $user->active = true;
    em()->persist($user);
    em()->flush();
    $v->set_success("Mot de passe mis à jour !");
    redirect("/");
}

?>

<article>
    <p>
        Vous activez le compte de
        <?= "{$token->user->first_name} {$token->user->last_name}" ?>.
    </p>

    <form method="post" class="row">
        <?= $v->render_validation() ?>
        <div class="col-sm-12 col-md-6 align-end">
            <h2 id="password">Mot de passe</h2>
            <legend>Mot de passe</legend>
            <?= $new_password->render() ?>
            <legend>Confirmation</legend>
            <?= $confirm_password->render() ?>
        </div>
        <div class="col-sm-12 col-md-6 align-end">
            <h2 id="password">Infos</h2>
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
        <input type="submit" class="outline" value="Enregistrer">
    </form>
</article>