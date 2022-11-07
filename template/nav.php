<?php
$nav_routes = [
    "/accueil" => "Accueil",
    "/mes-inscriptions" => "Mes inscriptions",
    "/les-licencies" => "Les licenciés",
    "/mon-profil" => "Mon profil"
];
?>

<nav class="container-fluid" id="main-menu">
    <ul class="icon">
        <li class="active">
            <a href="javascript:void(0);" onclick="toggleNav()">
                <i class="fa fa-bars"></i>
            </a>
        </li>
    </ul>
    <ul>
        <?php foreach ($nav_routes as $route => $nav_title) : ?>
            <li class="<?= $route == $_SESSION['current_route'] ? "active" : "" ?>"><a class="<?= $route == $_SESSION['current_route'] ? "active" : "contrast" ?>" href="<?= $route ?>"><?= $nav_title ?></a></li>
        <?php endforeach ?>
    </ul>
    <ul>
        <li><a class="contrast" href="logout">Déconnexion</a></li>
        <li>
            <details role="list" dir="rtl">
                <summary aria-haspopup="listbox" role="link" class="secondary">Theme</summary>
                <ul role="listbox">
                    <li><a href="#" data-theme-switcher="auto">Auto</a></li>
                    <li><a href="#" data-theme-switcher="light">Light</a></li>
                    <li><a href="#" data-theme-switcher="dark">Dark</a></li>
                </ul>
            </details>
        </li>
    </ul>

</nav>