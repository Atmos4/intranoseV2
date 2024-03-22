<?php

function renderDocument(SharedFile $shared_doc)
{
    $file_mime = $shared_doc->mime;

    $file_icon = match ($file_mime) {
        'pdf' => 'fas fa-file-pdf',
        'png', 'jpg', 'jpeg', 'gif' => 'fas fa-file-image',
        'doc', 'docx' => 'fas fa-file-word',
        'xls', 'xlsx' => 'fas fa-file-excel',
        'ppt', 'pptx' => 'fas fa-file-powerpoint',
        default => 'fas fa-file',
    };
    ?>
    <tr class="event-row">
        <td>
            <i class="<?= $file_icon ?> fa-lg"></i>
        </td>
        <td>
            <a href="/telecharger?id=<?= $shared_doc->id ?>" hx-boost=false>
                <?= $shared_doc->name ?>
                <i class="fas fa-download"></i>
            </a>
        </td>
        <td>
            <?= $shared_doc->date->format("d/m/Y") ?>
        </td>
        <td>
            <a href="/documents/<?= $shared_doc->id ?>/supprimer" class="destructive">
                <i class="fas fa-trash"></i>
            </a>
        </td>
    </tr>
<?php }

function renderTable($title, $docs)
{
    if (!$docs)
        return; ?>
    <thead>
        <tr>
            <th colspan="4">
                <?= $title ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($docs as $doc) {
            renderDocument($doc);
        } ?>
    </tbody>
<?php }