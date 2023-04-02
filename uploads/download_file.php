<?php
require_once "database/events.api.php";

$file_id = $_GET['id'];

$file = em()->find(SharedFile::class, $file_id);
$path = $file->path;


header("Content-Type: " . $file->mime);
header("Content-Disposition: attachment; filename=" . $path);
header("Content-Length: " . filesize($path));
readfile($path);

?>