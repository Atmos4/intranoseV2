<?php
restrict_access(Access::$EDIT_USERS);

$form = new Validator(action: "confirm-delete");

$group_id = get_route_param("group_id");
$group = em()->find(UserGroup::class, $group_id);
if (!$group) {
    force_404("the group of id $group_id doesn't exist");
}
if ($form->valid()) {
    logger()->info("Group {group_id} deleted by user {currentUserLogin}", ['group_id' => $group_id, 'currentUserLogin' => User::getCurrent()->login]);
    em()->remove($group);
    em()->flush();
    Toast::error("Groupe supprimé");
    redirect("/groupes");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Sûr de vouloir supprimer le groupe
            <?= "$group->name" ?> ? Il sera définitivement supprimé!!
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/groupes/<?= $group_id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>