<?php
restrict_dev();

$v_user = new Validator(action: "create_user");
$firstname = $v_user->text("firstname")->required()->placeholder("First name")->autocomplete("given-name");
$lastname = $v_user->text("lastname")->required()->placeholder("Last name")->autocomplete("family-name");

if ($v_user->valid()) {
    $fn = $firstname->value;
    $ln = $lastname->value;
    if ($result = SeedingService::createTestUser($fn, $ln, em())) {
        [$user, $pw] = $result;
        $v_user->set_success("Created user $user->first_name $user->last_name<br>"
            . "Login: $user->login<br>"
            . "Password: " . $pw);
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