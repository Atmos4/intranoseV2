<?php
// Recover global variables with $GLOBALS, because including the file puts them out of scope
$title = $GLOBALS['title'] ?? "";
$title .= " | Intranose";
$description = $GLOBALS['description'] ?? "";
$content = $GLOBALS['content'] ?? "";

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="assets/favicon/safari-pinned-tab.svg" color="#28b432">
    <meta name="msapplication-TileColor" content="#00a300">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Pico.css -->
    <link rel="stylesheet" href="assets/css/pico.min.css">
    <link rel="stylesheet" href="assets/css/main.css">

    <?php if (!empty($GLOBALS['css'])) : ?>
        <link rel="stylesheet" href="<?= $GLOBALS['css'] ?>">
    <?php endif ?>
</head>

<body>
    <?= $content ?>

    <script src="assets/js/nav.js"></script>
</body>

</html>