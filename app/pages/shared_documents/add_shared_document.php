<?php
restrict_access(Access::$ADD_EVENTS);

$v = new Validator();
$file_upload = $v->upload("file_upload")->required()->mime(UploadField::$FILE_MIME);
$permission = $v->select("permission")->options(array_column(Permission::cases(), 'value', 'name'))->label("Permission");


if ($v->valid()) {
    $shared_file = em()->getRepository(SharedFile::class)->findOneBy(['path' => $file_upload->file_name]);
    $shared_file ??= new SharedFile();
    $shared_file->set($file_upload->file_name, $file_upload->file_type);
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
    <div>
        <button type=" submit" class="outline">
            Enregistrer
        </button>
    </div>
</form>
</article>