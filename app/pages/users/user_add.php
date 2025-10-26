<?php
restrict_access(Access::$EDIT_USERS);

$v = new Validator([], "new_user_form");
$last_name = $v->text("last_name")->label("Nom")->placeholder("Nom")->required();
$first_name = $v->text("first_name")->label("Prénom")->placeholder("Prénom")->required();
$real_email = $v->email("real_email")->label("Adresse mail")->placeholder("Adresse mail")->required();
$birthdate = $v->date("birthdate")->label("Date de naissance")->required();

$permissions_array = ["USER" => "Utilisateur", "COACH" => "Coach", "STAFF" => "Administration"];
if (check_auth([Permission::ROOT])) {
    $permissions_array["ROOT"] = "Big Boss";
}

$permission = $v->select("permissions")->options($permissions_array)->label("Rôle")->required();

if ($v->valid()) {
    // DB
    $login = UserHelper::generateUserLogin($first_name->value, $last_name->value);
    $nose_email = !!env("INTRANOSE") ? UserHelper::generateUserEmail($first_name->value, $last_name->value) : "";

    $new_user = new User();
    $new_user->set_identity(strtoupper($last_name->value), $first_name->value, Gender::M);
    $new_user->birthdate = date_create($birthdate->value);
    $new_user->set_email($real_email->value, $nose_email);
    $new_user->permission = Permission::from($permission->value);
    $new_user->set_login($login);
    $new_user->status = UserStatus::INACTIVE;

    // validate user adding with redirections
    $token = new AccessToken($new_user, AccessTokenType::ACTIVATE, new DateInterval('P2D'));
    $result = MailerFactory::createActivationEmail($real_email->value, $token->id)->send();
    if ($result->success) {
        em()->persist($token);
        em()->persist($new_user);
        em()->flush();
        logger()->info("User {login} created and activation email sent", ["login" => $new_user->login]);
        Toast::success('Email envoyé!');
        redirect('/licencies');
    } else {
        logger()->warning("Attempt to create a user with email {$real_email->value} but activation email failed to send", ["error" => $result->message]);
        $form->set_error("Erreur lors de l'envoi de l'email d'activation");
        $form->set_error($result->message);
        return false;
    }
}




page("Nouveau licencié")->css("settings.css")->enableHelp();
?>
<?= actions()->back("/licencies")->submit("Créer", attributes: ["form" => "add-user"]) ?>
<form id="add-user" method="post" class="row">

    <?= $v->render_validation() ?>

    <div class="col-sm-12 col-md-6">
        <?= $last_name->render() ?>
        <?= $first_name->render() ?>
        <?= $birthdate->render() ?>
    </div>

    <div class="col-sm-12 col-md-6">
        <?= $real_email->render() ?>
        <div
            data-intro="Les différents rôles : <ul><li>Utilisateur : rôle de base</li><li>Coach et Administration : peuvent créer des événements et des licenciés</li></ul>">
            <?= $permission->render() ?>
        </div>
    </div>

</form>