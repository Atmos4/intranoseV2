<?php
$mainComponent = component(__DIR__ . "/realExample.php") ?>
<main>
    <?= $mainComponent->open(["title" => "Hello world", "actionLabel" => "Click"]) ?>
    <p>Welcome to my blog</p>
    <?= $mainComponent->close() ?>
</main>