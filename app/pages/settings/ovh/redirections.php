<?php
restrict_access();

require_once app_path() . "/components/conditional_icon.php";
require_once __DIR__ . "/SubmitButton.php";


$user_id = get_route_param("user_id");
$user = User::get($user_id) ?? throw new Exception("user not found");
$ovh = ovh_api();

switch ($_POST['action'] ?? "") {
    case "removeRedirection":
        $ovh->removeRedirection($user->nose_email, $user->real_email);
        logger()->info("Removed redirection", ['noseEmail' => $user->nose_email, "realEmail" => $user->real_email]);
        Toast::success("Redirection retirée");
        break;
    case "addRedirection":
        $ovh->addRedirection($user->nose_email, $user->real_email);
        logger()->info("Added redirection", ['noseEmail' => $user->nose_email, "realEmail" => $user->real_email]);
        Toast::success("Redirection ajoutée");
        break;

}

$redirection = $ovh->getRedirection(to: $user->real_email);
?>
<form data-loading-states class="col-sm-12 col-md-6" hx-trigger="submit"
    hx-post="/licencies/<?= $user->id ?>/ovh/redirections" hx-swap="outerHTML">
    <p>
        <?= ConditionalIcon($redirection) ?>
        <?= $redirection ? "Redirection à jour" : "Pas de redirection" ?>
    </p>
    <?= $redirection ?
        SubmitButton("removeRedirection", "Supprimer la redirection") :
        SubmitButton("addRedirection", "Activer la redirection") ?>
</form>