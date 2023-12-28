<?php
restrict_access();

$can_edit = check_auth(Access::$ADD_EVENTS);

# public files
$shared_files_users = em()->getRepository(SharedFile::class)->findBy(['permission_level' => Permission::USER]);

# upper authorisation files
if ($can_edit) {
    $shared_files_coach_staff = em()->getRepository(SharedFile::class)->findBy(['permission_level' => Access::$ADD_EVENTS]);
}

function render_documents($shared_doc)
{
    $file_mime = $shared_doc->mime;

    switch ($file_mime) {
        case 'pdf':
            $file_icon = 'fas fa-file-pdf';
            break;
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'gif':
            $file_icon = 'fas fa-file-image';
            break;
        case 'doc':
        case 'docx':
            $file_icon = 'fas fa-file-word';
            break;
        case 'xls':
        case 'xlsx':
            $file_icon = 'fas fa-file-excel';
            break;
        case 'ppt':
        case 'pptx':
            $file_icon = 'fas fa-file-powerpoint';
            break;
        default:
            $file_icon = 'fas fa-file';
            break;
    } ?>
    <tr class="event-row clickable" onclick="window.location.href = '/telecharger?id=<?= $shared_doc->id ?>'">
        <td>
            <i class="<?= $file_icon ?> fa-lg"></i>
        </td>
        <td>
            <?= $shared_doc->path ?>
        </td>
        <td><a href="/documents/<?= $shared_doc->id ?>/supprimer" class="destructive">
                <i class="fas fa-trash"></i>
            </a></td>
    </tr>
<?php }

page("Documents partagés");
?>

<?php if ($can_edit): ?>
    <nav id="page-actions">
        <a href="/documents/ajouter"><i class="fas fa-plus"></i> Ajouter un document</a>
    </nav>
<?php endif ?>

<?php if (count($shared_files_users)): ?>
    <?php if ($can_edit): ?>
        <h2>Documents publics</h2>
    <?php endif ?>
    <table role="grid">
        <thead class=header-responsive>
            <tr>
                <th></th>
                <th>Nom du fichier</th>
                <th></th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($shared_files_users as $shared_file) {
                render_documents($shared_file);
            } ?>

        </tbody>
    </table>
<?php endif ?>

<?php if ($can_edit && count($shared_files_coach_staff)): ?>
    <h2>Documents admins</h2>
    <table role="grid">
        <thead class=header-responsive>
            <tr>
                <th></th>
                <th>Nom du fichier</th>
                <th></th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($shared_files_coach_staff as $shared_file) {
                render_documents($shared_file);
            } ?>

        </tbody>
    </table>
<?php endif ?>

<?php if (!count($shared_files_users) && (!$can_edit || !count($shared_files_coach_staff))): ?>
    <p>Aucun document partagé</p>
<?php endif ?>