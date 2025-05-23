<?php
$page = Page::getInstance();
$clubColor = array_key_exists("selected_club", $_SESSION) ? ClubManagementService::create()->getClubColor($_SESSION["selected_club"] ?? null) : null; ?>
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
    <?php if ($clubColor != null): ?>
        <link rel="stylesheet" href="/assets/css/pico.<?= $clubColor ?>.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="/assets/css/pico.green.min.css">
    <?php endif; ?>
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
    <!-- <script src="/assets/js/notifications.js"></script> -->

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
    <?php if ($page->nav && has_session("user_id")): ?>
        <?= import(__DIR__ . "/topnav.php")(Feature::Messages->on(), $page->help, User::getMain()) ?>
        <?= component(__DIR__ . "/nav.php") ?>

    <?php endif ?>
    <div id="hx-indicator" aria-busy="true"></div>
    <main class="container" <?= $page->no_padding ? "style=\"padding:0\"" : "" ?>>


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