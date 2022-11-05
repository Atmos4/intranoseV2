<?php

function redirect($href)
{
    header("Location: " . $href);
    exit;
}

function require_root($path)
{
    require_once $_SERVER['DOCUMENT_ROOT'] . "/intranoseV2/" . $path;
}

/** 
 * Prepare page
 * @todo Improve naming
 */
function create_page($page_title, $page_css = null, $with_nav = true, $page_description = null)
{
    global $title, $description, $css;
    $title = $page_title;
    $description = $page_description;
    $css = $page_css;
    ob_start();
    if ($with_nav) {
        require_root("template/nav.php");
    }
}

/** Render page
 * @todo Improve naming
 */
function render_page()
{
    global $content;
    $content = ob_get_clean();
    require_root("template/layout.php");
}
