<?php
require_once "database/shared_docs.api.php";

$file_id = $_GET['id'];

$file_infos = get_shared_file($file_id);


header("Content-Type: " . $file_infos['mime']);
header("Content-Disposition: attachment; filename=" . $file_infos['path']);
header("Content-Length: " . filesize($file_infos['path']));
readfile($file_infos["path"]);

?>