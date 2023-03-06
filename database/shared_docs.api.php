<?php

function create_shared_docs_table()
{
    if (empty(fetch("SHOW TABLES LIKE 'shared_documents';"))) {
        return query_db("CREATE TABLE shared_documents(
            id INT AUTO_INCREMENT PRIMARY KEY,
            path VARCHAR(64),
            date DATETIME,
            size SMALLINT,
            mime VARCHAR(64)
        )");
    }
}

function set_shared_file($path, $date, $size, $mime)
{
    $result = query_db(
        "INSERT INTO shared_documents(path, date, size, mime) VALUES (?,?,?,?) 
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
    return $result;
}

function get_shared_files()
{
    return fetch("SELECT * FROM shared_documents");
}

function get_shared_file($id)
{
    return fetch_single(
        "SELECT * from shared_documents WHERE id = ?",
        $id
    );
}