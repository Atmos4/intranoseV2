<?php
$menu = MainMenu::create()
    ->addItem("Événements", "/evenements", "fa-calendar")
    ->addItem("Les licenciés", "/licencies", "fa-users")
    ->addItem("Mon profil", "/mon-profil", "fa-gear")
    ->addItem("Suggestions", "/feedback/nouveau", "fa-lightbulb")
    ->addItem("Documents", "/documents", "fa-file")
;

if (env('DEVELOPMENT')) {
    $menu->addItem("Dev", "/dev", "fa-code");
}
$main_user = User::getMain();

if (check_auth([Permission::ROOT])) {
    $menu->addItem("Feedbacks", "/feedback-list", "fa-bug")
        ->addItem("Logs", "/admin/logs", "fa-file-waveform");
}

?>
<div id="nav-button">
    <button class="outline contrast" onclick="openNav()">
        &#9776; Menu
    </button>
</div>
<aside id="mySidenav" class="sidenav notvisible">
    <nav>
        <ul>
            <li>
                <h3 class="nav-title">Intranose</h3>
            </li>
            <?php foreach ($menu->items as $menu_item): ?>
                <li class="<?= strpos($_SESSION['current_route'], $menu_item->url) !== false ? "active" : "" ?>">
                    <a class="<?= strpos($_SESSION['current_route'], $menu_item->url) !== false ? "active" : "contrast" ?>"
                        href="<?= $menu_item->url ?>" <?= $menu_item->disableBoost ? 'hx-boost="false"' : '' ?>>
                        <?php if ($menu_item->icon): ?> <i class="fa fa-fw <?= $menu_item->icon ?>"></i>
                        <?php endif ?>
                        <?= " " . $menu_item->label ?>
                    </a>
                </li>
            <?php endforeach ?>
            <?php if ($main_user->family_leader): ?>
                <li>
                    <details class="dropdown" id="family-dropdown">
                        <summary role="link" class="contrast"><i class="fa fa-fw fa-users"></i> Famille
                        </summary>
                        <ul>
                            <?php foreach ($main_user->family->members as $member):
                                if ($member !== $main_user): ?>
                                    <li><a href="/user-control/<?= $member->id ?>">
                                            <?= $member->first_name ?>
                                        </a>
                                    </li>
                                <?php endif;
                            endforeach ?>
                            <li><a href="/famille/<?= $main_user->family->id ?>" preload><i class="fa fa-gear"></i>
                                    Gérer...</a>
                            </li>
                        </ul>
                    </details>
                </li>
            <?php endif ?>
        </ul>
        <div class="icon-buttons">
            <?php include app_path() . "/components/theme_switcher.php" ?>

            <?php if ($main_user): ?>
                <a href="/logout" hx-boost="false" role=button class="outline contrast destructive" title="Déconnexion">
                    <i class="fa fa-power-off"></i>
                </a>
            <?php endif ?>
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