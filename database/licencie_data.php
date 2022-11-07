<?php
function get_all_licencies()
{
    return fetch("SELECT * FROM licencies WHERE valid=1 AND invisible=0 ORDER BY nom");
}
function get_licencie($id)
{
    $data = fetch("SELECT * FROM licencies WHERE id = ? LIMIT 1", $id);
    if (!count($data)) {
        include_once "pages/404.php";
        exit;
    }
    return $data[0];
}
