<?php
restrict_access(Access::$ADD_EVENTS);
$file_id = $_GET['id'];

$db = DB::getInstance();
$file = $db->em()->find(SharedFile::class, $file_id);
$path = Path::uploads($file->path);

if (file_exists($path)) {
    header("Content-Type: " . $file->mime);
    header('Content-Disposition: attachment; filename="' . $file->name . '"');
    header("Content-Length: " . filesize($path));
    readfile($path);
    exit;
}