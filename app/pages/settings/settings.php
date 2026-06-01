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
    $user = User::get($user_id);
} else {
    $user = User::getCurrent();
}

if (!$user) {
    echo "This user doesn't exist";
    return;
}

$is_visiting |= Page::getInstance()->controlled;
$can_reset_credentials = $is_visiting && check_auth([Permission::ROOT]) && $user->permission != Permission::ROOT;

$can_change_permission = check_auth(Access::$EDIT_USERS) && $is_visiting;

// Identity
$user_infos = [
    "last_name" => $user->last_name,
    "first_name" => $user->first_name,
    "gender" => $user->gender->value,
    "phone" => $user->phone,
    "birthdate" => date_format($user->birthdate, "Y-m-d"),
    "permission" => $user->permission->value
];

$v_infos = new Validator($user_infos, "identity_form");
$last_name = $v_infos->text("last_name")->label("Nom")->placeholder()->required();
$first_name = $v_infos->text("first_name")->label("Prénom")->placeholder()->required();
$birthdate = $v_infos->date("birthdate")->label("Date de naissance")->required();
$gender = $v_infos->text("gender")->label("Sexe");
$phone = $v_infos->phone("phone")->label("Numéro de téléphone")->placeholder();

if ($can_change_permission) {
    $permissions_array = ["USER" => "Utilisateur", "COACH" => "Coach", "STAFF" => "Administration"];
    if (check_auth([Permission::ROOT])) {
        $permissions_array["ROOT"] = "Big Boss";
    }
    $permission = $v_infos->select("permission")->options($permissions_array)->label("Rôle")->required();
}

if ($v_infos->valid()) {
    $user->set_identity($last_name->value, $first_name->value, Gender::from($gender->value));

    $user->phone = $phone->value;
    $user->birthdate = date_create($birthdate->value);
    if ($can_change_permission) {
        $oldLevel = $user->permission->value;
        $user->permission = Permission::from($permission->value);
        logger()->info("Permission changed for user {login} from {oldLevel} to {newLevel}", [
            "login" => $user->login,
            "oldLevel" => $oldLevel,
            "newLevel" => $user->permission->value,
        ]);
    }
    em()->persist($user);
    em()->flush();
    Toast::create("Identité mise à jour !");
}

// Emails
$use_nose_email = !!env("INTRANOSE");
$can_change_nose_email = check_auth([Permission::ROOT]);
$can_change_emails = check_auth(Access::$EDIT_USERS);

$user_emails = [
    "real_email" => $user->real_email,
    "nose_email" => $user->nose_email,
];
$v_emails = new Validator($user_emails, action: "emails_form");
$real_email = $v_emails->email("real_email")->label("Adresse mail perso")->placeholder();
$can_change_emails ? $real_email->required() : $real_email->readonly();

if ($use_nose_email) {
    $nose_email = $v_emails->email("nose_email")->label("Adresse mail nose")->placeholder();
    $can_change_nose_email ? $nose_email->required() : $nose_email->readonly();
}

if ($v_emails->valid() && $can_change_emails) {
    UserManagementService::changeEmails($user, $can_change_nose_email && $use_nose_email ? $nose_email->value : null, $real_email->value, $use_nose_email);
}

// Picture
$profile_picture = $user->getPicture();

$image_mime_types = [
    'jpg' => ['image/jpeg'],
    'png' => ['image/png'],
    'gif' => ['image/gif']
];
$v_picture = new Validator(action: "picture_form");
$picture = $v_picture->upload("picture")->mime($image_mime_types)->max_size(2 * 1024 * 1024);

if ($v_picture->valid()) {
    $picture->set_file_name($user->id);
    if ($path = $picture->save_file(Path::profilePicture())) {
        $user->replacePicture($path);
        $v_picture->set_success("Photo de profil mise à jour !");
        em()->flush();
        $profile_picture = $path;
    } else {
        $v_picture->set_error("Erreur lors de la mise à jour de la photo de profil");
    }
}

page($is_visiting ? "Profil - $user->first_name $user->last_name" : "Mon profil")->css("settings.css");
?>

<form method="post" class="row center" enctype="multipart/form-data" id="pictureForm">
    <?= $v_picture->render_validation() ?>
    <label class="profile" title="Modifier la photo de profil">
        <img class="profile-picture" src="<?= $profile_picture ?>">
        <i class="fa fa-pen"></i>
        <?= $picture
            ->attributes([
                "style" => "width: auto",
                "onchange" => "document.getElementById('pictureForm').submit()",
                "class" => "hidden"
            ])
            ->render() ?>
    </label>
</form>

<?= actions(check_auth(Access::$ROOT))->link("/licencies/$user->id/debug", "Debug", "fa-laptop-code") ?>

<h2 id="identity">Infos</h2>

<form method="post" hx-swap="innerHTML show:#identity:top" class="row">
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
        <?= $birthdate->render() ?>
        <?= $phone->render() ?>
        <?php if ($can_change_permission): ?>
            <?= $permission->render() ?>
        <?php endif ?>
    </div>


    <div>
        <input type="submit" class="outline" name="submitIdentity" value="Mettre à jour les infos">
    </div>
</form>
<hr>

<h2 id="emails">Email</h2>

<form method="post" hx-swap="innerHTML show:#emails:top" class="row">
    <?= $v_emails->render_validation() ?>
    <?php if (!$use_nose_email): ?>
        <div class="col-12">
            <?= $real_email->render() ?>
        </div>
    <?php else: ?>
        <div class="col-sm-12 col-md-6">
            <?= $real_email->render() ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $nose_email->render() ?>
        </div>
    <?php endif ?>
    <?php if ($can_change_emails): ?>
        <div>
            <input type="submit" class="outline" name="submitEmails" value="Mettre à jour">
        </div>
    <?php endif ?>
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
        Toast::create("Login mis à jour !");
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
    Toast::create("Mot de passe mis à jour !");
}
?>
<?php if (!$is_visiting || $can_reset_credentials): ?>
    <div class="row">
        <form method="post" hx-swap="innerHTML show:#login:top" class="col-sm-12 col-md-6 align-end">
            <h2 id="login">Login</h2>
            <?= $v_login->render_validation() ?>
            <?= "Login actuel: $user->login" ?>
            <?= $new_login->render() ?>
            <input type="submit" class="outline" name="submitLogin" value="Changer le login">
        </form>
        <form method="post" hx-swap="innerHTML show:#password:top" class="col-sm-12 col-md-6 align-end">
            <h2 id="password">Mot de passe</h2>
            <?= $v_password->render_validation() ?>
            <?php if (!$can_reset_credentials): ?>
                <input type="text" name="username" value="<?= $user->login ?>" autocomplete="username" class="hidden">
                <?= $current_password->render() ?>
                <?= $new_password->render() ?>
                <?= $confirm_password->render() ?>
            <?php endif ?>
            <input type="submit" class="outline" name="submitPassword"
                value="<?= $can_reset_credentials ? "Réinitialiser" : "Changer le mot de passe" ?>">
        </form>
    </div>
<?php endif ?>

<h2 id="groups">Groupes</h2>
<form method="post">
    <?= GroupService::renderTags($user->groups) ?>
    <?php if (check_auth(Access::$EDIT_USERS)): ?>
        <?php
        $v_groups = new Validator(action: "groups_form");
        if ($v_groups->valid()) {
            GroupService::processUserGroupChoice($user);
            em()->flush($user);
            reload();
        }
        ?>
        <?= $v_groups->render_validation() ?>
        <?= GroupService::renderUserGroupChoice($user) ?>
        <input type="submit" class="outline" name="submitGroups" value="Mettre à jour les groupes" ?>
    <?php endif ?>
</form>
<script src="/assets/js/copy-clipboard.js"></script>
<script src="/assets/js/shoelace-forms.js"></script>