<?php
restrict_access(Access::$ADD_EVENTS);
$file_id = $_GET['id'];

$file = em()->find(SharedFile::class, $file_id);
$path = $file->path;


header("Content-Type: " . $file->mime);
header("Content-Disposition: attachment; filename=" . $file->name);
header("Content-Length: " . filesize($path));
readfile($path);

?>