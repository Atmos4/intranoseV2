<?php
restrict_access();

$user_id = get_route_param("user_id", false);
$is_visiting = false;
if ($user_id) {
    restrict_access(Access::$EDIT_USERS);
    if ($user_id == $_SESSION['user_id']) {
        redirect("/mon-profil");
    }
    $is_visiting = true;
}

$user = em()->find(User::class, $user_id ?? User::getCurrent());
if (!$user) {
    echo "This user doesn't exist";
    return;
}
$is_visiting |= Page::getInstance()->controlled;
$can_reset_credentials = $is_visiting && check_auth([Permission::ROOT]) && $user->permission != Permission::ROOT;


$user_infos = [
    "last_name" => $user->last_name,
    "first_name" => $user->first_name,
    "gender" => $user->gender->value,
    "real_email" => $user->real_email,
    "nose_email" => $user->nose_email,
    "phone" => $user->phone
];

$v_infos = new Validator($user_infos, "identity_form");
$last_name = $v_infos->text("last_name")->label("Nom")->placeholder()->required();
$first_name = $v_infos->text("first_name")->label("Prénom")->placeholder()->required();
$real_email = $v_infos->email("real_email")->label("Addresse mail perso")->placeholder()->required();
$nose_email = $v_infos->email("nose_email")->label("Addresse mail nose")->placeholder();
$gender = $v_infos->text("gender")->label("Sexe");
$phone = $v_infos->phone("phone")->label("Numéro de téléphone")->placeholder();

if ($is_visiting) {
    $nose_email->required();
} else {
    $nose_email->disabled();
}

if ($v_infos->valid()) {
    $user->set_identity($last_name->value, $first_name->value, Gender::from($gender->value));
    $user->set_email($real_email->value, $nose_email->value);
    $user->phone = $phone->value;
    em()->persist($user);
    em()->flush();
    $v_infos->set_success("Identité mise à jour !");
}


page($is_visiting ? "Profil - $user->first_name $user->last_name" : "Mon profil")->css("settings.css");
?>
<?php if ($is_visiting): ?>
    <nav id="page-actions">
        <a href="/licencies/<?= $user->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    </nav>
<?php endif ?>

<h2 id="identity">Infos</h2>

<form method="post" action="#identity" class="row">
    <?= $v_infos->render_validation() ?>
    <div class="col-sm-12 col-md-6">
        <?= $last_name->render() ?>
        <?= $first_name->render() ?>
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

    <div class="col-sm-12 col-md-6">
        <?= $real_email->render() ?>
        <?= $nose_email->render() ?>
    </div>

    <?= $phone->render() ?>

    <button type="submit" class="outline" name="submitIdentity" class=col-md-4>Mettre à jour
        les infos</button>
</form>
<hr>

<?php
// Login
$v_login = new Validator(action: "login_form");
$current_login = $v_login->text("current_login")->placeholder("Login actuel")->autocomplete("username");
$new_login = $v_login->text("new_login")->placeholder("Nouveau login")->required()->min_length(3);
if (!$can_reset_credentials) {
    $current_login->required();
    $current_login->condition($user->login == $current_login->value, "Mauvais login");
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

// Password
$v_password = new Validator(action: "password_form");
if (!$can_reset_credentials) {
    $current_password = $v_password->password("current_password")
        ->placeholder("Mot de passe actuel")
        ->required()
        ->autocomplete("current-password");
    $new_password = $v_password->password("new_password")->autocomplete("new-password")->placeholder("Nouveau mot de passe")->required()->secure();
    $confirm_password = $v_password->password("confirm_password")
        ->autocomplete("new-password")
        ->placeholder("Confirmer le mot de passe")
        ->required();
    $current_password->condition(password_verify($current_password->value ?? "", $user->password), "Mauvais mot de passe");
    $confirm_password->condition($new_password->value == $confirm_password->value, "Les deux mots de passe sont différents");
}

if ($v_password->valid()) {
    if ($can_reset_credentials) {
        $user->set_password($user->first_name);
    } else {

        $user->set_password($new_password->value);
    }
    em()->persist($user);
    em()->flush();
    $v_password->set_success("Mot de passe mis à jour !");
}
?>


<?php if (!$is_visiting || $can_reset_credentials): ?>
    <div class="row">
        <form method="post" action="#login" class="col-sm-12 col-md-6 align-end">
            <h2 id="login">Login</h2>
            <?= $v_login->render_validation() ?>
            <?= $can_reset_credentials ? "Login acuel: $user->login" : $current_login->render() ?>
            <?= $new_login->render() ?>
            <input type="submit" class="outline" name="submitLogin" value="Changer le login">
        </form>
        <form method="post" action="#password" class="col-sm-12 col-md-6 align-end">
            <h2 id="password">Mot de passe</h2>
            <?= $v_password->render_validation() ?>
            <?php if (!$can_reset_credentials): ?>
                <input type="hidden" autocomplete="username" name="username" value="<?= $user->login ?>">
                <?= $current_password->render() ?>
                <?= $new_password->render() ?>
                <?= $confirm_password->render() ?>
            <?php endif ?>
            <input type="submit" class="outline" name="submitPassword"
                value="<?= $can_reset_credentials ? "Réinitialiser" : "Changer le mot de passe" ?>">
        </form>
    </div>
<?php endif ?>