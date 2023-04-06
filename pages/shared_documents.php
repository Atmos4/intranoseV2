<?php
restrict_access(Access::$ADD_EVENTS);

$v = new Validator();
$file_upload = $v->upload("file_upload")->mime(UploadField::$FILE_MIME);

if ($v->valid()) {
    $shared_file = em()->getRepository(SharedFile::class)->findOneBy(['path' => $file_upload->file_name]);
    $shared_file ??= new SharedFile();
    $shared_file->set($file_upload->file_name, $file_upload->file_type);
    if ($file_upload->save_file()) {
        em()->persist($shared_file);
        em()->flush();
    }
}

$shared_files = em()->getRepository(SharedFile::class)->findAll();

function render_documents($shared_doc)
{ ?>
    <tr class="event-row clickable" onclick="window.location.href = '/download?id=<?= $shared_doc->id ?>'">
        <td>
            <i class="fas fa-file"></i>
        </td>
        <td>
            <?= $shared_doc->path ?>
        </td>
    </tr>
<?php }

page("Documents partagés");

?>

<h3>Ajouter un document</h3>
<form method="post" enctype="multipart/form-data">
    <?= $v->render_validation() ?>
    <div class="row">
        <div class="col-auto">
            <?= $file_upload->render() ?>
        </div>
        <div class="col-auto">
            <button type="submit" class="outline">
                Enregistrer
            </button>
        </div>
    </div>
</form>
</article>

<h3>Documents enregistrés</h3>

<table role="grid">
    <?php if (count($shared_files)): ?>
        <thead class=header-responsive>
            <tr>
                <th></th>
                <th>Nom du fichier</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($shared_files as $shared_file) {
                render_documents($shared_file);
            } ?>

        </tbody>
    <?php else: ?>
        <p class="center">Pas de fichiers pour le moment 🫠</p>
    <?php endif ?>
</table>