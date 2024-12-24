<?php
restrict_management();
$slug = Router::getParameter("slug", pattern: '/[\w-]+/');
$backupService = new BackupService(dbPath: DB::forClub($slug)->sqlitePath);

$v_backup = new Validator(action: "create_backup");
if ($v_backup->valid()) {
    $backupService->createBackup();
}
$backups = $backupService->getBackups(); ?>
<section>
    <p>
        <?= count($backups) . "/" . BackupService::MAX_BACKUPS ?>
    </p>
    <ul>
        <?php foreach ($backups as $b): ?>
            <li>
                <a href="/admin/backups/download?backup=<?= basename($b) ?>" hx-boost=false>
                    <?= basename($b) ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
</section>
<section>
    <?= $v_backup ?>
    <button>Create backup</button>
</section>