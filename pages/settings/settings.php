<?php
restrict_access();

$user_id = get_route_param("user_id", false);
if ($user_id) {
    restrict_access(Access::$EDIT_USERS);
}
$can_visit = check_auth(Access::$EDIT_USERS);

$user = em()->find(User::class, $user_id ?? $_SESSION['user_id']);
if (!$user) {
    echo "This user doesn't exist";
    return;
}


$user_identity = [
    "last_name" => $user->last_name,
    "first_name" => $user->first_name,
    "licence" => $user->licence,
    "gender" => $user->gender->value
];

$v_identity = new Validator($user_identity, "identity_form");
$last_name = $v_identity->text("last_name")->label("Prénom")->placeholder()->required();
$first_name = $v_identity->text("first_name")->label("Nom")->placeholder()->required();
$licence = $v_identity->number("licence")->label("Numéro de licence");

if ($can_visit) {
    $licence->required();
} else {
    $licence->disabled();
    $licence->value ??= $user->licence;
}

$gender = $v_identity->text("gender")->label("Sexe");

$user_email = [
    "real_email" => $user->real_email,
    "nose_email" => $user->nose_email,
];

$v_login = new Validator(action: "login_form");
$current_login = $v_login->text("current_login")->placeholder("Login actuel")->required()->autocomplete("username");
$new_login = $v_login->text("new_login")->placeholder("Nouveau login")->required()->min_length(3);
$current_login->condition($user->login == $current_login->value, "Mauvais login");

$v_password = new Validator(action: "password_form");
$current_password = $v_password->password("current_password")
    ->placeholder("Mot de passe actuel")
    ->required()
    ->autocomplete("current-password");
$new_password = $v_password->password("new_password")->autocomplete("new-password")->placeholder("Nouveau mot de passe")->required()->secure();
$confirm_password = $v_password->password("confirm_password")
    ->autocomplete("new-password")
    ->placeholder("Confirmer le mot de passe")
    ->required()
    ->secure();
$current_password->condition(password_verify($current_password->value, $user->password), "Mauvais mot de passe");
$confirm_password->condition($new_password->value == $confirm_password->value, "Les deux mots de passe sont différents");

$v_email = new Validator($user_email, "email_form");
$real_email = $v_email->email("real_email")->label("Addresse mail perso")->placeholder()->required();
$nose_email = $v_email->email("nose_email")->label("Addresse mail nose")->placeholder()->required();

$user_perso = [
    "sportident" => $user->sportident,
    "address" => $user->address,
    "postal_code" => $user->postal_code,
    "city" => $user->city,
    "phone" => $user->phone
];

$v_perso = new Validator($user_perso, "infos_form");
$sportident = $v_perso->number("sportident")->label("Numéro SportIdent")->required();
$address = $v_perso->text("address")->label("Adresse")->placeholder()->required();
$postal_code = $v_perso->number("postal_code")->label("Code postal")->required();
$city = $v_perso->text("city")->label("Ville")->placeholder()->required();
$phone = $v_perso->phone("phone")->label("Numéro de téléphone")->placeholder()->required();

if ($v_identity->valid()) {
    $user->set_identity($last_name->value, $first_name->value, $licence->value, Gender::from($gender->value));
    em()->persist($user);
    em()->flush();
    $v_identity->set_success("Identité mise à jour !");
}

if ($v_login->valid()) {
    $users_with_same_login = em()->getRepository(User::class)->findByLogin($new_login->value);
    if (count($users_with_same_login)) {
        $new_login->set_error("Ce login est déjà utilisé");
    } else {
        $user->set_login($new_login->value);
        em()->persist($user);
        em()->flush();
        $v_login->set_success("Login mis à jour !");
    }
}

if ($v_password->valid()) {
    $user->set_password($new_password->value);
    em()->persist($user);
    em()->flush();
    $v_password->set_success("Mot de passe mis à jour !");
}

if ($v_email->valid()) {
    $user->set_email($real_email->value, $nose_email->value);
    em()->persist($user);
    em()->flush();
    $v_email->set_success("Emails mis à jour !");
}

if ($v_perso->valid()) {
    $user->set_perso($sportident->value, $address->value, $postal_code->value, $city->value, $phone->value);
    em()->persist($user);
    em()->flush();
    $v_perso->set_success("Infos perso mises à jour !");
}


page("Mon profil");
?>
<div id="page-actions">
    <?php if ($can_visit && $user_id): ?>
        <a href="/licencies/<?= $user->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <?php endif ?>
</div>

<h2 id="identity">Identité</h2>

<form method="post" action="#identity">
    <?= $v_identity->render_validation() ?>
    <div class="grid">
        <?= $last_name->render() ?>
        <?= $first_name->render() ?>
    </div>

    <div class="grid">
        <?= $licence->render() ?>
        <fieldset>
            <legend>Sexe</legend>
            <label for="man">
                <input type="radio" id="man" name="gender" value=<?= Gender::M->value ?> <?= ($user->gender == Gender::M) ? 'checked="checked"' : ''; ?>>
                Homme
            </label>
            <label for="woman">
                <input type="radio" id="woman" name="gender" value=<?= Gender::W->value ?> <?= ($user->gender == Gender::W) ? 'checked="checked"' : ''; ?>>
                Dame
            </label>
        </fieldset>
    </div>

    <button type="submit" class="outline" name="submitIdentity" class=col-md-4>Mettre à jour
        l'identité</button>
</form>

<hr>

<div class="row">
    <form method="post" class="col-sm-12 col-md-6">
        <h2 id="login">Login</h2>
        <?= $v_login->render_validation() ?>
        <?= $current_login->render() ?>
        <?= $new_login->render() ?>
        <input type="submit" class="outline" name="submitLogin" value="Mettre à jour">
    </form>
    <form method="post" action="#password" class="col-sm-12 col-md-6">
        <h2 id="password">Mot de passe</h2>

        <input type="hidden" autocomplete="username" name="username" value="<?= $user->login ?>">
        <?= $v_password->render_validation() ?>
        <?= $current_password->render() ?>
        <?= $new_password->render() ?>
        <?= $confirm_password->render() ?>
        <input type="submit" class="outline" name="submitPassword" value="Mettre à jour">
    </form>
</div>

<hr>

<h2 id="emails">Emails</h2>

<form method="post" action="#emails">
    <?= $v_email->render_validation() ?>
    <?= $real_email->render() ?>
    <?= $nose_email->render() ?>
    <button type="submit" class="outline" name="submitEMail" class=col-md-4>Mettre à jour les mails</button>
</form>

<hr>

<h2 id="infos-perso"> Infos perso </h2>

<form method="post" action="#infos-perso">
    <?= $v_perso->render_validation() ?>
    <?= $sportident->render() ?>
    <?= $address->render() ?>


    <div class="grid">
        <?= $postal_code->render() ?>
        <?= $city->render() ?>
    </div>

    <?= $phone->render() ?>

    <button type="submit" class="outline" name="submitInfos" class=col-md-4>Mettre à jour les infos</button>
</form>