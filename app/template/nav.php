<?php
$menu = MainMenu::create()
    ->addItem("Événements", "/evenements", "fa-calendar")
    ->addItem("Les licenciés", "/licencies", "fa-users")
    ->addItem("Documents", "/documents", "fa-file")
    ->addItem("Liens utiles", "/liens-utiles", "fa-arrow-up-right-from-square");

if (dev_or_staging()) {
    $menu->addItem("Dev", "/dev", "fa-code");
}

if (check_auth(Access::$ADD_EVENTS)) {
    $menu->addItem("Paramètres club", "/club_settings", "fa-screwdriver-wrench");
}

if (check_auth([Permission::ROOT])) {
    $menu
        ->addItem("Feedbacks", "/feedback-list", "fa-bug")
        ->addItem("Admin", "/admin", "fa-file-waveform");
} ?>
<aside id="mySidenav" class="sidenav notvisible">
    <nav>
        <ul>
            <li>
                <h2 class="nav-title">
                    <?php import(__DIR__ . "/../components/linklub_logo.php")(!env("INTRANOSE")) ?>
                </h2>
            </li>
            <?php foreach ($menu->items as $menu_item): ?>
                <li>
                    <a class="<?= getMenuClass($menu_item->url) ?>" href="<?= $menu_item->url ?>"
                        <?= $menu_item->disableBoost ? 'hx-boost="false"' : '' ?>>
                        <?php if ($menu_item->icon): ?> <i class="fa fa-fw <?= $menu_item->icon ?>"></i>
                        <?php endif ?>
                        <?= " " . $menu_item->label ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
        <div class="icon-buttons">
            <?php include app_path() . "/components/theme_switcher.php" ?>
            <a href="/feedback/nouveau" class="icon-button <?= getMenuClass("/feedback") ?>" title="Suggestions">
                <i class="fa fa-lightbulb"></i>
            </a>
        </div>
    </nav>
</aside>
<script>
    function openNav() {
        const navBar = document.getElementById('mySidenav');
        if (navBar.classList.contains('open')) {
            navBar.classList.remove('open');
            setTimeout(() => { navBar.classList.add("notvisible") }, 200);
        }
        else {
            navBar.classList.remove("notvisible");
            navBar.classList.add('open');
        }
    }
</script>