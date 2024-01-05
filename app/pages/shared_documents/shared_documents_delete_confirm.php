<?php
restrict_access([Permission::ROOT]);

$form = new Validator(action: "confirm-delete");

$doc_id = get_route_param("doc_id");
$shared_doc = SharedFile::get($doc_id);
if (!$shared_doc) {
    $form->set_error("Le document numéro $doc_id n'existe pas");
}
if ($form->valid()) {
    if (unlink(app_path() . "/uploads/" . $shared_doc->path)) {
        logger()->info("File {filePath} deleted by user {currentUserLogin}", ['filePath' => $shared_doc->path, 'currentUserLogin' => User::getCurrent()->login]);
        em()->remove($shared_doc);
        em()->flush();
        Toast::error("Document supprimé");
        redirect("/documents");
    } else {
        $form->set_error("Impossible de supprimer le fichier");
        logger()->error("File {filePath} not deleted by user {currentUserLogin}", ['filePath' => $shared_doc->path, 'currentUserLogin' => User::getCurrent()->login]);
    }
}

page("Confirmation de suppression");
?>
<form method="post">
    <div class="row center">
        <?= $form->render_validation() ?>
        <p>Sûr de vouloir supprimer le document
            <?= "$shared_doc->path" ?> ? Il sera définitivement supprimé!!
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/licencies/desactive">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>