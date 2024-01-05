<?php
restrict_access(Access::$ADD_EVENTS);

$v = new Validator();
$file_upload = $v->upload("file_upload")->required()->mime(UploadField::$FILE_MIME);
$permission = $v->select("permission")->options(["USER" => 'Public', "COACH" => "Admin"])->label("Niveau de permission");
$name = $v->text("name")->label("Nom du fichier");


if ($v->valid()) {
    $shared_file = em()->getRepository(SharedFile::class)->findOneBy(['path' => $file_upload->file_name]);
    $shared_file ??= new SharedFile();
    $shared_file->name = $name->value ? $name->value . "." . strtolower(pathinfo($file_upload->file_name, PATHINFO_EXTENSION)) : $file_upload->file_name;
    $shared_file->mime = $file_upload->file_type;
    $file_upload->set_file_name(bin2hex(random_bytes(4)) . "." . strtolower(pathinfo($file_upload->file_name, PATHINFO_EXTENSION)));
    $shared_file->path = $file_upload->file_name;
    $shared_file->permission_level = Permission::from($permission->value);
    if ($file_upload->save_file()) {
        em()->persist($shared_file);
        em()->flush();
    }
}

page("Ajouter un document");
?>
<nav id="page-actions">
    <a href="/documents" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
</nav>
<form method="post" enctype="multipart/form-data">
    <?= $v->render_validation() ?>
    <div>
        <?= $file_upload->render() ?>
    </div>

    <div class="col-sm-6 col-12">
        <?= $permission->render() ?>
    </div>

    <div class="col-sm-6 col-12">
        <?= $name->render() ?>
    </div>
    <div>
        <button type=" submit" class="outline">
            Enregistrer
        </button>
    </div>
</form>