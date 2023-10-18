<?php
restrict_access(Access::$EDIT_USERS);

$v = new Validator([], "new_user_form");
$last_name = $v->text("last_name")->label("Nom")->placeholder("Nom")->required();
$first_name = $v->text("first_name")->label("Prénom")->placeholder("Prénom")->required();
$real_email = $v->email("real_email")->label("Addresse mail")->placeholder("Addresse mail")->required();
$birthdate = $v->date("birthdate")->label("Date de naissance")->required();

$permissions_array = ["USER" => "Utilisateur", "COACH" => "Coach", "COACHSTAFF" => "Coach/Responsable", "GUEST" => "Guest", "STAFF" => "Responsable"];
$user = em()->find(User::class, User::getCurrent());
if ($user->permission == Permission::ROOT) {
    $permissions_array["ROOT"] = "Big Boss";
}

$permissions = $v->select("permissions")->options($permissions_array)->label("Permissions")->required();

if ($v->valid()) {
    require_once app_path() . "/utils/userUtils.php";
    // DB
    $login = UserHelper::generateUserLogin($first_name->value, $last_name->value);
    $nose_email = UserHelper::generateUserEmail($first_name->value, $last_name->value);

    $new_user = new User();
    $new_user->set_identity(strtoupper($last_name->value), $first_name->value, Gender::M);
    $new_user->birthdate = date_create($birthdate->value);
    $new_user->set_email($real_email->value, $nose_email);
    $new_user->permission = Permission::from($permissions->value);
    $new_user->set_login($new_login);
    $new_user->status = UserStatus::INACTIVE;

    //Setup OVH redirections
    $ovh = ovh_api();

    // Send Email
    $token = new AccessToken($new_user, AccessTokenType::ACTIVATE, new DateInterval('P2D'));

    $result = MailerFactory::createActivationEmail($real_email->value, $token->id)->send();
    if ($result->success) {
        logger()->info("User {$new_user->id} created by user {$user->id} and activation email sent");
        em()->persist($token);
        em()->persist($new_user);
        em()->flush();
        $v->set_success('Email envoyé!');
    } else {
        logger()->warning("User {$user->id} tried to create user {$new_user->id} but activation email failed to send");
        $v->set_error($result->message);
    }
}




page("Nouveau licencié")->css("settings.css");
?>
<nav id="page-actions">
    <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <button type="submit" form="add-user">Créer</button>
</nav>
<form id="add-user" method="post" class="row">

    <?= $v->render_validation() ?>

    <div class="col-sm-12 col-md-6">
        <?= $last_name->render() ?>
        <?= $first_name->render() ?>
        <?= $birthdate->render() ?>
    </div>

    <div class="col-sm-12 col-md-6">
        <?= $real_email->render() ?>
        <?= $permissions->render() ?>
    </div>

</form>