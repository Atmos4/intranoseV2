<?php
restrict_access(Access::$ROOT);
$service = new BackupService;
$v = new Validator(action: "create_backup");
if ($v->valid()) {
    $service->createBackup();
}
page("SQLite backups") ?>
<form method="post">
    <?= $v ?>
    <?= actions()->back("/admin")->submit("Create backup") ?>
</form>
<section>
    <div>Oldest</div>
    <ul>
        <?php foreach ($service->getBackups() as $b): ?>
            <li>
                <a href="/admin/backups/download?backup=<?= basename($b) ?>" hx-boost=false>
                    <?= basename($b) ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
    <div>Newest</div>
</section>