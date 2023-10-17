<?php
restrict_dev();

$v_user = new Validator(action: "create_user");
$firstname = $v_user->text("firstname")->required()->placeholder("First name")->autocomplete("given-name");
$lastname = $v_user->text("lastname")->required()->placeholder("Last name")->autocomplete("family-name");

if ($v_user->valid()) {
    $fn = $firstname->value;
    $ln = $lastname->value;
    $login = strtolower($ln . "_" . substr($fn, 0, 1));
    if (!User::getByLogin($login)) {
        $newUser = new User();
        $newUser->last_name = $ln;
        $newUser->first_name = $fn;
        $newUser->login = $login;
        $newUser->password = password_hash(strtolower($fn), PASSWORD_DEFAULT);
        $newUser->nose_email = strtolower("$fn.$ln@nose42.fr");
        $newUser->real_email = "test@example.com";
        $newUser->phone = "0612345678";
        $newUser->permission = Permission::ROOT;
        $newUser->gender = Gender::M;
        $newUser->birthdate = date_create("1996-01-01");
        $newUser->status = UserStatus::ACTIVE;
        em()->persist($newUser);
        em()->flush();
        $v_user->set_success("Created user $newUser->first_name $newUser->last_name<br>"
            . "Login: $newUser->login<br>"
            . "Password: " . strtolower($fn));
    } else {
        $v_user->set_error("User already exists");
    }
}


page("Create test user") ?>
<form method="post">
    <?= $v_user->render_validation() ?>
    <?= $firstname->render() ?>
    <?= $lastname->render() ?>
    <button type="submit">Créer</button>
</form>