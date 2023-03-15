<?php
$migration_files = list_files("database/migrations");
//$migrations_in_db = query_db("SELECT * FROM migrations");
foreach ($migration_files as $file) {
    echo $file . "<br/>";
}