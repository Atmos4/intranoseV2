<?php
$page = Page::getInstance(); ?>
<!doctype html>
<html lang="en">

<head hx-head=merge>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= implode(" | ", array_filter([$page->title, "Intranose"])) ?>
    </title>
    <meta name="description" content="<?= $page->description ?>">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="/assets/favicon/safari-pinned-tab.svg" color="#28b432">
    <meta name="msapplication-TileColor" content="#00a300">
    <meta name="theme-color" content="#14a91b">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/solid.min.css">

    <!-- Theme switcher -->
    <link rel="stylesheet" href="/assets/css/open-props-easings.css">
    <link rel="stylesheet" href="/assets/css/theme-toggle.css">

    <!-- Pico.css -->
    <link rel="stylesheet" href="/assets/css/picov2.min.css">
    <link rel="stylesheet" href="/assets/css/bsg.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">

    <!-- Custom CSS -->
    <?php foreach ($page->css_files as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach ?>

    <?php if ($page->nav): ?>
        <link rel="stylesheet" href="/assets/css/navbar.css">
    <?php endif ?>

    <!-- HTMX -->
    <script src="/assets/js/htmx1.9.5-core.min.js" defer></script>
    <script src="/assets/js/htmx1.9.5-head.js" defer></script>
    <script src="/assets/js/htmx1.9.5-loading.js" defer></script>

    <script src="/assets/js/theme.js"></script>
</head>

<body hx-ext="head-support,loading-states" hx-boost="true" hx-indicator="#hx-indicator"
    hx-on:show-modal="document.getElementById(event.detail.modalId).showModal()">
    <?php
    if ($page->nav && has_session("user_id")) {
        require_once app_path() . "/template/nav.php";
    } ?>
    <div id="hx-indicator" aria-busy="true"></div>
    <main class="container">
        <?php if ($page->controlled) {
            echo ControlNotice();
        } ?>
        <?php if ($page->heading !== false): ?>
            <h2 class="center">
                <?= $page->heading ?: $page->title ?>
            </h2>
        <?php endif ?>
        <?= $page->content ?>
    </main>

    <?= Toast::renderRoot() ?>
</body>


</html>