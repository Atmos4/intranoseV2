<?php
restrict_access();
require_once "utils/form_validation.php";
$admin = check_auth(Permission::ROOT);

$user_id = get_route_param("user_id", false);
if ($user_id) {
    restrict_access(Permission::ROOT);
} else {
    $user_id = $_SESSION['user_id'];
}

$user = em()->find(User::class, $user_id);
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

$v_identity = validate($user_identity, "identity_form");
$last_name = $v_identity->text("last_name")->label("Prénom")->placeholder()->required();
$first_name = $v_identity->text("first_name")->label("Nom")->placeholder()->required();
$licence = $v_identity->number("licence")->label("Numéro de licence")->required();

if (!$admin) {
    $licence->disabled();
}

$gender = $v_identity->text("gender")->label("Sexe");

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
$phone = $v_perso->phone("phone")->label("Numéro de téléphone")->placeholder()->required();

if ($v_identity->valid()) {
    $user->set_identity($last_name->value, $first_name->value, $licence->value, Gender::from($gender->value));
    em()->persist($user);
    em()->flush();
    $v_identity->set_success("Identité mise à jour !");
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

    <button type="submit" name="submitIdentity" class=col-md-4>Mettre à jour l'identité</button>
</form>

<hr>

<h2 id="mon-compte">Compte</h2>

<form method="post" action="#mon-compte">
    <?= $v_email->render_validation() ?>
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

    <button type="submit" name="submitInfos" class=col-md-4>Mettre à jour les infos</button>
</form>