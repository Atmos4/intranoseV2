<?php
restrict_access([Permission::ROOT]);

$form = new Validator(action: "confirm-delete");

$user_id = get_route_param("user_id");
$user = em()->find(User::class, $user_id);
if (!$user) {
    $form->set_error("the user of id $user_id doesn't exist");
}
if ($user->status != UserStatus::DEACTIVATED) {
    $form->set_error("Can't delete an active user");
}
if ($form->valid()) {
    logger()->info("User {$user->id} deleted by user " . User::getCurrent());
    em()->remove($user);
    em()->flush();
    redirect("/licencies");
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