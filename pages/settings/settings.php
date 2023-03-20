<?php
restrict_access();

require_once "database/settings.api.php";
require_once "utils/form_validation.php";


//might be changed later for admins
$user_id = $_SESSION['user_id'];
$user = em()->find(User::class, $user_id);


$user_identity = [
    "last_name" => $user->last_name,
    "first_name" => $user->first_name,
    "licence" => $user->licence,
    "gender" => $user->gender->value
];

$v_identity = validate($user_identity, "identity_form");
$last_name = $v_identity->text("last_name")->label("Prénom")->placeholder()->required();
$first_name = $v_identity->text("first_name")->label("Nom")->placeholder()->required();
$licence = $v_identity->number("licence")->label("Numéro de licence")->required();
$gender = $v_identity->text("gender")->label("Sexe")->placeholder()->required();

$user_email = [
    "real_email" => $user->real_email,
    "nose_email" => $user->nose_email,
];

$v_email = validate($user_email, "email_form");
$real_email = $v_email->email("real_email")->label("Addresse mail perso")->placeholder()->required();
$nose_email = $v_email->email("nose_email")->label("Addresse mail nose")->placeholder()->required();

$user_perso = [
    "sportident" => $user->sportident,
    "address" => $user->address,
    "postal_code" => $user->postal_code,
    "city" => $user->city,
    "phone" => $user->phone
];

$v_perso = validate($user_perso, "infos_form");
$sportident = $v_perso->number("sportident")->label("Numéro SportIdent")->required();
$address = $v_perso->text("address")->label("Adresse")->placeholder()->required();
$postal_code = $v_perso->number("postal_code")->label("Code postal")->required();
$city = $v_perso->text("city")->label("Ville")->placeholder()->required();
$phone = $v_perso->phone("phone")->label("Nuémro de téléphone")->placeholder()->required();


if ($v_identity->valid()) {
    $user->set_identity($last_name->value, $first_name->value, $licence->value, $gender->value);
    em()->persist($user);
    em()->flush();
    $validation_result = "Identité mise à jour !";
}

if ($v_email->valid()) {
    $user->set_email($real_email->value, $nose_email->value);
    em()->persist($user);
    em()->flush();
    $validation_result = "Emails mis à jour !";
}

if ($v_perso->valid()) {
    $user->set_perso($sportident->value, $address->value, $postal_code->value, $city->value, $phone->value);
    em()->persist($user);
    em()->flush();
    $validation_result = "Infos perso mises à jour !";
}


page("Mon profil");
?>

<?php if (isset($validation_result)): ?>
    <p class="success">
        <?= $validation_result ?>
    </p>
<?php endif ?>

<h2>Identité</h2>


<form method="post">
    <?= $v_identity->render_errors() ?>
    <div class="grid">
        <?= $last_name->render() ?>
        <?= $first_name->render() ?>
    </div>

    <div class="grid">
        <?= $licence->render() ?>
        <?= $gender->render() ?>
    </div>

    <button type="submit" name="submitIdentity" class=col-md-4>Mettre à jour l'identité</button>
</form>

<hr>

<h2 id="mon-compte">Compte</h2>


<form method="post">
    <?= $v_email->render_errors() ?>
    <div class="grid">
        <button type=button class="secondary" onclick="window.location.href = '/mon-profil/changement-mdp'">Changer le
            mot de passe</button>
        <button type=button class="secondary" onclick="window.location.href = '/mon-profil/changement-login'">Changer le
            login</button>
    </div>

    <div class="grid">
        <?= $real_email->render() ?>
        <?= $nose_email->render() ?>
    </div>

    <button type="submit" name="submitEMail" class=col-md-4>Mettre à jour les mails</button>
</form>

<hr>

<h2> Infos perso </h2>


<form method="post">
    <?= $v_perso->render_errors() ?>
    <?= $sportident->render() ?>
    <?= $address->render() ?>


    <div class="grid">
        <?= $postal_code->render() ?>
        <?= $city->render() ?>
    </div>

    <?= $phone->render() ?>

    <button type="submit" name="submitInfos" class=col-md-4>Mettre à jour les infos</button>
</form>