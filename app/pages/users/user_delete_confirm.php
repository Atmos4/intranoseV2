<?php
restrict_access([Permission::ROOT]);

$form = new Validator(action: "confirm-delete");

$user_id = get_route_param("user_id");
$user = User::get($user_id);
if (!$user) {
    $form->set_error("L'utilisateur numéro $user_id n'existe pas");
}
if ($user->status != UserStatus::DEACTIVATED) {
    $form->set_error("Impossible de supprimer un utilisateur actif");
}
if ($form->valid()) {
    logger()->info("User {login} deleted by user {currentUserLogin}", ['login' => $user->login, 'currentUserLogin' => User::getCurrent()->login]);
    em()->remove($user);
    em()->flush();
    redirect("/licencies/desactive");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Sûr de vouloir supprimer
            <?= "$user->first_name $user->last_name" ?> ? Il sera définitivement supprimé!!
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/licencies/desactive">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">SUPPRIMER</button>
        </div>
    </div>
</form>