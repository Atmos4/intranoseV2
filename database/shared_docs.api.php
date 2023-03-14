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
            CONSTRAINT UF UNIQUE (path,mime)
        )");
    }
}

function set_shared_file($path, $date, $size, $mime)
{
    $test_file_exists = fetch("SELECT * FROM shared_documents WHERE path = ? AND mime = ?;", $path, $mime);
    if (empty($test_file_exists)) {
        $result = query_db(
            "INSERT INTO shared_documents(path, date, size, mime)
            VALUE(?,?,?,?);",
            $path,
            $date,
            $size,
            $mime
        );
    } else {
        $result = query_db(
            "UPDATE shared_documents SET path=?, date=?, size=?, mime=? WHERE path=? AND mime=?",
            $path,
            $date,
            $size,
            $mime,
            $path,
            $mime
        );
    }
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