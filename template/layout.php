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

    <!-- Pico.css -->
    <link rel="stylesheet" href="assets/css/pico.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">

    <?php if (!empty($css)) : ?>
        <link rel="stylesheet" href="<?= $css ?>">
    <?php endif ?>
</head>

<body>
    <?= $content ?>

    <script src="assets/js/theme-switcher.js"></script>
</body>

</html>