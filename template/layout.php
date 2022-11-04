<?php

$title ??= "";
$title .= " | Intranose";
$description ??= "";
$content ??= "";

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">

    <!-- Pico.css -->
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
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