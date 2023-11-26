<?php
restrict_access();

require_once app_path() . "/components/conditional_icon.php";
require_once __DIR__ . "/SubmitButton.php";


$user_id = get_route_param("user_id");
$user = User::get($user_id) ?? throw new Exception("user not found");

[$isSubscribed, $polling] = OvhService::create()->updateUserInNoseMailingList($user, $_POST['action'] ?? null);

?>
<form data-loading-states class="col-sm-12 col-md-6" method="post" hx-indicator="this"
    hx-trigger="<?= $polling ? "submit, load delay:2s" : "submit" ?>" hx-post="/licencies/<?= $user->id ?>/ovh/mailing"
    hx-swap="outerHTML">
    <p>
        <?= ConditionalIcon($isSubscribed) ?>Membre de la liste <code>nose</code>
        <?php if ($polling): ?>
            <a tabindex="0" class="contrast" data-tooltip="Mise à jour en cours"><i class="fa fa-circle-question"></i></a>
        <?php endif ?>
    </p>
    <?= $isSubscribed ?
        SubmitButton("removeFromMailing", "Se désabonner", $polling) :
        SubmitButton("addToMailing", "S'abonner", $polling) ?>
</form>