<?php
restrict_access([Permission::ROOT]);

$form = new Validator(action: "confirm-deactivate");

$user_id = get_route_param("user_id");
$user = em()->find(USER::class, $user_id);
if (!$user) {
    $form->set_error("L'utilisateur numéro $user_id n'existe pas");
}
if ($form->valid()) {
    OvhService::create()->deactivateUser($user);
}

page("Confirmation de désactivation");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Sûr de vouloir désactiver cet utilisateur ?</p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/licencies?user=<?= $user->id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Désactiver</button>
        </div>
    </div>
</form>