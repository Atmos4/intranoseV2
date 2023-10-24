<?php
$menu = MainMenu::getInstance()
    ->addItem("Événements", "/evenements", "fa-calendar")
    ->addItem("Les licenciés", "/licencies", "fa-users")
    ->addItem("Mon profil", "/mon-profil", "fa-gear");

if (env('DEVELOPMENT')) {
    $menu->addItem("Dev", "/dev", "fa-code");
}
$main_user = User::getMain();

?>
<div id="nav-button">
    <button class="outline contrast" onclick="document.getElementById('mySidenav').classList.toggle('open')">
        &#9776; Menu
    </button>
</div>
<aside id="mySidenav" class="sidenav">
    <nav>
        <ul>
            <li>
                <h3 class="nav-title">Intranose</h3>
            </li>
            <?php foreach ($menu->items as $menu_item): ?>
                <li class="<?= $menu_item->url == $_SESSION['current_route'] ? "active" : "" ?>">
                    <a class="<?= $menu_item == $_SESSION['current_route'] ? "active" : "contrast" ?>"
                        href="<?= $menu_item->url ?>" <?= $menu_item->disableBoost ? 'hx-boost="false"' : '' ?>>
                        <?php if ($menu_item->icon): ?> <i class="fas <?= $menu_item->icon ?>"></i>
                        <?php endif ?>
                        <?= " " . $menu_item->label ?>
                    </a>
                </li>
            <?php endforeach ?>
            <?php if ($main_user->family_leader): ?>
                <li>
                    <details role="list" id="family-dropdown">
                        <summary role="link" class="contrast"><i class="fa fa-users"></i> Famille
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
            <?php if ($main_user): ?>
                <li><a class="destructive" href="/logout"><i class="fa fa-power-off"></i> Déconnexion</a>
                </li>
            <?php endif ?>
        </ul>
        <ul>
            <li>
                <?php include app_path() . "/components/theme_switcher.php" ?>
            </li>
        </ul>
    </nav>
</aside>