<?php
$c = component(__DIR__ . "/compWithSections.php");
$noHeader = !Component::prop("noHeader"); ?>
<main>
    <?= $c->open(["class" => "container"]) ?>

    <?php if ($noHeader): ?>
        <?= $c->start("header") ?>
        <h1>Hello world</h1>
        <?= $c->stop() ?>
    <?php endif ?>

    <p>Main content</p>

    <?= $c->start("footer") ?>
    <cite>Made with love</cite>
    <?= $c->stop() ?>

    <?= $c->close() ?>
</main>