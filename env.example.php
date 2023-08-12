<?php
/** Example environment file. 
 * To have a valid configuration:
 * - create a file named env.php
 * - copy the content of this file
 * - change the variables according to your environement */
return [
    // SQL Database setup
    "db_host" => "localhost",
    "db_name" => "mydatabase",
    "db_user" => "root",
    "db_password" => "",

    // Keep this false for production. Enables debug info
    "developement" => true,

    //PHPMailer setup
    "mail_host" => "example.com",
    "mail_user" => "hello.world@example.com",
    "mail_password" => "strongPassword",
];