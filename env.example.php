<?php
/** Example environment file. 
 * To have a valid configuration:
 * - create a file named env.php
 * - copy the content of this file
 * - change the variables according to your environement */
return [
    // SQL Database host:
    "db_host" => "localhost",
    // SQL Database name:
    "db_name" => "mydatabase",
    // SQL Database username:
    "db_user" => "root",
    // SQL Database password:
    "db_password" => "",
    // Keep this false for production:
    "developement" => true
];