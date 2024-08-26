<?php
// keep in sync with .htaccess
if (preg_match('/(\.png|\.jpg|\.webp|\.gif|\.jpeg|\.zip|\.css|\.svg|\.js|\.ttf|\.woff2|\.webmanifest|\.pdf|\.doc|\.docx|\.xls|\.xlsx)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
} else {
    include __DIR__ . "/routes.php";
}