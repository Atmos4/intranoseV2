<?php
restrict_access(Access::$ADD_EVENTS);

$v = new Validator();
$file_upload = $v->upload("file_upload")->required()->mime(UploadField::$FILE_MIME);
$permission = $v->select("permission")->options(["USER" => 'Public', "COACH" => "Coachs et Administration"])->label("Niveau de permission");
$name = $v->text("name")->label("Nom du fichier (optionel)");

if ($v->valid()) {
    $shared_file = em()->getRepository(SharedFile::class)->findOneBy(['path' => $file_upload->file_name]);
    $shared_file ??= new SharedFile();
    $shared_file->name = $name->value ? $name->value . "." . $file_upload->get_ext() : $file_upload->file_name;
    $shared_file->mime = $file_upload->file_type;
    $file_upload->set_file_name(date("YmdHis"));
    $shared_file->path = $file_upload->file_name;
    $shared_file->permission_level = Permission::from($permission->value);
    if ($file_upload->save_file(Path::uploads())) {
        em()->persist($shared_file);
        em()->flush();
    }
}

page("Ajouter un document")->enableHelp();
?>
<?= actions()->back("/documents") ?>
<form method="post" enctype="multipart/form-data">
    <?= $v->render_validation() ?>
    <i>Formats autorisés: images, PDF, Word, Excel. S'il manque des formats <a href="/feedback">faites une
            suggestion!</a></i>
    <div data-intro="Ajoutez des documents ici">
        <?= $file_upload->render() ?>
    </div>
    <div
        data-intro="Vous pouvez décider de qui à accès aux documents. <b>Public</b> signifie tout le monde, <b>Admin</b> correspond aux coachs et à l'administration.">
        <?= $permission->render() ?>
    </div>
    <div data-intro="En option, le nom d'affichage du fichier.">
        <?= $name->render() ?>
    </div>
    <button type=" submit" class="outline">
        Enregistrer
    </button>
</form>