<?php
restrict_access(Access::$ROOT);
$log_files = glob(Path::LOGS . "/*.log");
page("Log list") ?>
<?= actions()->back("/admin") ?>
<ul>
    <?php foreach ($log_files as $f): ?>
        <li><a href="/admin/logs/<?= basename($f) ?>">
                <?= basename($f) ?>
            </a></li>
    <?php endforeach ?>
</ul>