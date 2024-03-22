<?php
restrict_access();

$canEdit = check_auth(Access::$ADD_EVENTS);

# public files
$publicDocs = SharedFile::findBy(Permission::USER);

# upper authorisation files
$adminDocs = $canEdit ? SharedFile::findBy(Access::$ADD_EVENTS) : null;

include __DIR__ . "/renderDocument.php";
page("Documents partagés");
?>

<?php if ($canEdit): ?>
    <nav id="page-actions">
        <a href="/documents/ajouter"><i class="fas fa-plus"></i> Ajouter un document</a>
    </nav>
<?php endif ?>

<?php
if (!$publicDocs && !$adminDocs): ?>

    <p class=center>Aucun document pour le moment 🫠</p>

    <?php return;
endif;
?>

<table role="grid">

    <?php
    renderTable("Documents publics", $publicDocs);

    if ($adminDocs)
        renderTable("Documents privés", $adminDocs);
    ?>

</table>