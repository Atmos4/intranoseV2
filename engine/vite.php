<?php
const VITE_HOST = 'http://localhost:5133';
const VITE_LOCK_FILE = __DIR__ . "/../usingVite.lockfile";

function foundOnViteServer(string $entry): bool
{
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }
    if (!file_exists(VITE_LOCK_FILE)) {
        return $exists = false;
    }
    return true;
    // Commenting this part out as I don't want to make it too complex at first
    // The code below will reach at the server every render and check if the vite server is running
    // $handle = curl_init(VITE_HOST . '/' . $entry);
    // curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($handle, CURLOPT_NOBODY, true);

    // curl_exec($handle);
    // $error = curl_errno($handle);
    // curl_close($handle);

    // if ($error) {
    //     unlink(VITE_LOCK_FILE);
    // }

    // return $exists = !$error;
}


// Helpers to print tags
function viteScripts(string $entry = "assets/vite.js"): string
{
    if (!foundOnViteServer($entry)) {
        return "";
    }
    return '<script type="module" src="' . VITE_HOST . '/@vite/client"></script>' . "\n"
        . '<script type="module" src="' . VITE_HOST . '/' . $entry . '"></script>';
}