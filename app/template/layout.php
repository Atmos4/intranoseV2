<?php
$page = Page::getInstance(); ?>
<!doctype html>
<html lang="en">

<head hx-head=merge>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= implode(" | ", array_filter([$page->title, config("name", "Linklub")])) ?>
    </title>
    <meta name="description" content="<?= $page->description ?>">

    <?php include __DIR__ . "/favicon.php" ?>

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

    <!-- Intro.js -->
    <link href="/assets/css/introjs.css" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/navbar.css">

    <!-- HTMX -->
    <script src="/assets/js/htmx1.9.5-core.min.js" defer></script>
    <script src="/assets/js/htmx1.9.5-head.js" defer></script>
    <script src="/assets/js/htmx1.9.5-loading.js" defer></script>

    <script src="/assets/js/theme.js"></script>

    <!-- Notifications -->
    <script src="/assets/js/notifications.js"></script>

    <!-- Shoelace -->
    <?php include __DIR__ . "/shoelace.php" ?>

    <!-- Intro.js -->
    <script src="/assets/js/intro.min.js"></script>
    <script>function start_intro() { introJs().start() }</script>

    <!-- Custom JS -->
    <?php foreach ($page->scripts as $script): ?>
        <script src="<?= $script ?>" defer></script>
    <?php endforeach ?>
</head>

<body hx-ext="head-support,loading-states" <?= has_session("user_id") || $page->_boost ? 'hx-boost="true"' : "" ?>
    hx-indicator="#hx-indicator" hx-on:show-modal="document.getElementById(event.detail.modalId).showModal()">
    <?php
    if ($page->nav && has_session("user_id")) {
        require_once app_path() . "/template/nav.php";
    } ?>
    <div id="hx-indicator" aria-busy="true"></div>
    <main class="container" <?= $page->no_padding ? "style=\"padding:0\"" : "" ?>>
        <?php if ($page->help): ?>
            <div class="help-button" onclick="start_intro()" id="help-button"><i class="fas fa-question"></i></div>
        <?php endif ?>
        <?php if ($page->controlled) {
            echo ControlNotice();
        } ?>
        <?php if ($page->heading !== false): ?>
            <h2 class="center main-heading">
                <?= $page->heading ?: $page->title ?>
            </h2>
        <?php endif ?>
        <?= $page->content ?>
    </main>

    <?= Toast::renderRoot() ?>
</body>

</html>