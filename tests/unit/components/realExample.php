<article>
    <h2><?= Component::prop("title") ?></h2>
    <?= Component::children() ?>
    <footer>
        <button><?= Component::prop("actionLabel") ?></button>
    </footer>
</article>