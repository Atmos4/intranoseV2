<?php
restrict_access(Access::$EDIT_USERS);

$user_id = get_route_param("user_id");
$guardian_id = get_route_param("guardian_id");

$user = em()->find(User::class, $user_id);
if (!$user) {
    force_404("Cet utilisateur n'existe pas");
}

$guardian = em()->find(Guardian::class, $guardian_id);
if (!$guardian) {
    force_404("Ce tuteur n'existe pas");
}

$form = new Validator(action: "confirm-delete-guardian");

if ($form->valid()) {
    $user->guardians->removeElement($guardian);
    em()->remove($guardian);
    em()->flush();
    Toast::create("Tuteur supprimé !");
    redirect("/licencies/$user_id/modifier");
}

page("Supprimer un tuteur");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Supprimer le tuteur
            <strong><?= htmlspecialchars("$guardian->first_name $guardian->last_name", ENT_QUOTES) ?></strong> de
            <?= htmlspecialchars("$user->first_name $user->last_name", ENT_QUOTES) ?> ?
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/licencies/<?= $user_id ?>/modifier#guardians">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" class="destructive"><i class="fas fa-trash"></i> Supprimer</button>
        </div>
    </div>
</form>