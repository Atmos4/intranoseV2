<?php
restrict_access(Access::$EDIT_USERS);

$v = new Validator([], "new_user_form");
$last_name = $v->text("last_name")->label("Nom")->placeholder("Nom")->required();
$first_name = $v->text("first_name")->label("Prénom")->placeholder("Prénom")->required();
$real_email = $v->email("real_email")->label("Addresse mail perso")->placeholder("Addresse mail perso")->required();
$gender = $v->text("gender")->label("Sexe")->required();

$sportident = $v->number("sportident")->label("Numéro SportIdent")->placeholder()->min_length(5);
$phone = $v->phone("phone")->label("Numéro de téléphone")->placeholder();

if ($v->valid()) {
    $login = strtolower(substr($first_name->value, 0, 1) . $last_name->value);
    $list_login_numbers = User::getBySubstring($login);
    $max_number = $list_login_numbers ? (max($list_login_numbers) ? max($list_login_numbers) + 1 : 1) : 0;
    $user_same_name = User::findByFirstAndLastName($first_name->value, $last_name->value);
    $nose_email = strtolower($first_name->value . "." . $last_name->value) . (count($user_same_name) ?? '') . "@nose42.fr";
    $new_user = new User();
    $new_user->set_identity(strtoupper($last_name->value), $first_name->value, Gender::from($gender->value));
    $new_user->set_email($real_email->value, $nose_email);
    $new_user->set_password($first_name->value);
    $new_user->phone = $phone->value;
    $max_number ? $new_login = $login . $max_number : $new_login = $login;
    $new_user->set_login($new_login);
    em()->persist($new_user);
    em()->flush();
    $v->set_success("Nouveau licencié créé !");
}




page("Nouveau licencié")->css("settings.css");
?>
<form method="post" class="row">
    <nav id="page-actions">
        <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

        <div>
            <button type="submit">
                Créer
            </button>
        </div>
    </nav>

    <?= $v->render_validation() ?>

    <div class="col-sm-12 col-md-6">
        <?= $last_name->render() ?>
        <?= $real_email->render() ?>
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

    <div class="col-sm-12 col-md-6">
        <?= $first_name->render() ?>
    </div>

    <?= $phone->render() ?>

</form>