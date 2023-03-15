<?php
restrict_access("ROOT", "STAFF", "COACH", "COACHSTAFF");

require_once "database/shared_docs.api.php";
require_once "utils/form_validation.php";

$id = $_SESSION['user_id'];

create_shared_docs_table();


$v = validate();
$file_upload = $v->upload("file_upload")->set_target_dir("uploads/shared_docs/")->label("Téléchargement");

if (!empty($_FILES) && $v->valid()) {
    $date = date('Y-m-d h:i:s');
    if (set_shared_file($file_upload->get_name(), $date, $file_upload->get_size(), $file_upload->get_type())) {
        $success = $file_upload->save_file();
    } else {
        $error = "Problème à l'enregistrement";
    }
}

$shared_files = get_shared_files();

function render_documents($shared_doc)
{ ?>
    <tr class="event-row clickable" onclick="window.location.href = '/download_shared_files?id=<?= $shared_doc['id'] ?>'">
        <td>
            <i class="fas fa-file"></i>
        </td>
        <td>
            <?= $shared_doc["path"] ?>
        </td>
    <?php }

page("Documents partagés");

?>

    <h3>Ajouter un document</h3>
    <?= $v->render_errors() ?>
    <?php if (isset($success)): ?>
        <p class="success">
            <?= $success ?>
        </p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error">
            <?= $error ?>
        </p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="center">
            <?= $file_upload->render() ?>
        </div>
        <div>
            <button type="submit">
                Enregistrer
            </button>
        </div>
    </form>
    </article>

    <h3>Documents enregistrés</h3>

    <table role="grid">
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
    </table>