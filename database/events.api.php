<?php

// TODO: remove
function get_file($id)
{
    return fetch_single(
        "SELECT * from circulaires WHERE id = ?",
        $id
    );
}

function set_file($event_id, $path, $date, $size, $mime)
{
    $result = query_db(
        "INSERT INTO circulaires(path, date, size, mime) VALUES (?,?,?,?) 
        ON DUPLICATE KEY UPDATE path = ?, date= ?, size = ?, mime = ?;",
        $path,
        $date,
        $size,
        $mime,
        $path,
        $date,
        $size,
        $mime
    );
    $circu_id = fetch_single("SELECT id 
    FROM circulaires 
    WHERE date = ?;", $date)[0];
    $result = $result && query_db("UPDATE deplacements 
    SET circu = ?
    WHERE did = ?
    LIMIT 1;", $circu_id, $event_id);
    return $result;
}