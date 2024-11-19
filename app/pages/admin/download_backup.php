<?php
restrict_access(Access::$ROOT);
$backup = $_GET['backup'];
$path = (new BackupService)->getBackupFile($backup);

if (file_exists($path)) {
    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header("Content-Length: " . filesize($path));
    readfile($path);
    exit;
}