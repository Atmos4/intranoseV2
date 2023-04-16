<?php
restrict_access(Access::$EDIT_USERS);

$user_id = get_route_param("user_id");
$user = em()->find(USER::class, $user_id);
if (!$user) {
    echo "the user of id $user_id doesn't exist";
    return;
}
if (!empty($_POST) and isset($_POST['delete'])) {
    $user->active = false;
    em()->flush();
    redirect("/licencies");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <p>Sûr de vouloir désactiver cet utilisateur ?</p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/licencies/<?= $user->id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Désactiver</button>
        </div>
    </div>
</form>