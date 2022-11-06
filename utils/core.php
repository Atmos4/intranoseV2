<?php

function redirect($href)
{
    header("Location: " . $href);
    exit;
}

function require_root($path)
{
    require_once $_SERVER['DOCUMENT_ROOT'] . "/" . $path;
}

/** 
 * Setup page
 * @todo Improve naming
 */
function page($page_title, $page_css = null, $with_nav = true, $page_description = null)
{
    global $title, $description, $css;
    $title = $page_title;
    $description = $page_description;
    $css = $page_css;
    if ($with_nav) {
        require_root("template/nav.php");
    }
}
