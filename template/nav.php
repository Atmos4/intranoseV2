<?php
$nav_routes = [
    "/evenements" => ["Événements", "fa-calendar"],
    "/licencies" => ["Les licenciés", "fa-users"],
    "/mon-profil" => ["Mon profil", "fa-gear"]
];
$current_user = User::getCurrent();
/* if (check_auth(Access::$ADD_EVENTS)) {
$nav_routes["/documents"] = ["Documents partagés", "fa-file"];
} */
?>

<nav class="container-fluid" id="main-menu">
    <ul class="responsive icon">
        <li>
            <a href="javascript:void(0);" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>
    <ul>
        <?php foreach ($nav_routes as $route => $nav_title): ?>
            <li class="<?= $route == $_SESSION['current_route'] ? "active" : "" ?>"><a
                    class="<?= $route == $_SESSION['current_route'] ? "active" : "contrast" ?>" href="<?= $route ?>"><i
                        class="fas <?= $nav_title[1] ?>"></i>
                    <?= " " . $nav_title[0] ?>
                </a></li>
        <?php endforeach ?>
        <?php if ($current_user->family_leader): ?>
            <li>
                <details role="list">
                    <summary aria-haspopup="listbox" role="link" class="contrast">Famille</summary>
                    <ul role="listbox">
                        <?php foreach ($current_user->family->members as $member):
                            if ($member !== $current_user): ?>
                                <li><a>
                                        <?= $member->first_name ?>
                                    </a></li>
                            <?php endif;
                        endforeach ?>
                    </ul>
                </details>
            </li>
        <?php endif ?>
    </ul>
    <ul>
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
        <li><a class="destructive" href="/logout">Déconnexion</a></li>
    </ul>

</nav>