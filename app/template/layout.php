<?php
$page = Page::getInstance(); ?>
<!doctype html>
<html lang="en">

<head>
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
    <meta name="theme-color" content="#ffffff">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/solid.min.css">

    <!-- Theme switcher -->
    <link rel="stylesheet" href="/assets/css/open-props-easings.css">
    <link rel="stylesheet" href="/assets/css/theme-toggle.css">

    <!-- Pico.css -->
    <link rel="stylesheet" href="/assets/css/pico.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">

    <?php
    if ($page->nav) { ?>
        <link rel="stylesheet" href="/assets/css/navbar.css">
    <?php } ?>
    <script src="https://unpkg.com/htmx.org@1.9.3"
        integrity="sha384-lVb3Rd/Ca0AxaoZg5sACe8FJKF0tnUgR2Kd7ehUOG5GCcROv5uBIZsOqovBAcWua" crossorigin="anonymous"
        defer></script>
    <script defer src="/assets/js/nav.js"></script>


    <?php foreach ($page->css_files as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endforeach ?>
</head>

<body>
    <?php
    if ($page->nav) {
        require_once app_path() . "/template/nav.php";
    } ?>
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
</body>

</html>