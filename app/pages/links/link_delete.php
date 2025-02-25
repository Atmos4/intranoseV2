<?php
restrict_access();

$form = new Validator(action: "confirm-delete");

$link_id = get_route_param("link_id");
$link = em()->find(Link::class, $link_id);
if (!$link) {
    force_404("the link of id $link_id doesn't exist");
}
if ($form->valid()) {
    logger()->info("Link {link_id} deleted by user {currentUserLogin}", ['link_id' => $link_id, 'currentUserLogin' => User::getCurrent()->login]);
    em()->remove($link);
    em()->flush();
    Toast::error("Lien supprimé");
    redirect("/liens-utiles");
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Sûr de vouloir supprimer le lien
            <?= "$link->button_text" ?> ? Il sera définitivement supprimé!!
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/liens-utiles">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>