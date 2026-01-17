<?php
return function (bool $messages, bool $help = false, User $main_user = null) { ?>
    <nav class="topnav">
        <ul>
            <li>
                <button class="outline contrast menu-button" onclick="openNav()">
                    &#9776; Menu
                </button>
            </li>
        </ul>
        <ul>

            <?php if ($help): ?>
                <li>
                    <button class="outline contrast" onclick="start_intro()">
                        <sl-tooltip content="Aide"><i class="fas fa-question"></i></sl-tooltip>
                    </button>
                </li>
            <?php endif ?>
            <?php if ($messages): ?>
                <li>
                    <a role="button" class="outline <?= getMenuClass("/messages") ?>" href="/messages">
                        <sl-tooltip content="Messages"><i class="fas fa-message"></i></sl-tooltip>
                    </a>
                </li>
            <?php endif ?>

            <li>
                <sl-dropdown class="dropdown-nav" distance="10">
                    <button slot="trigger" class="outline <?= getMenuClass("/mon-profil") ?>">
                        <?= IconText("fa-cog", "Paramètres") ?>
                    </button>
                    <aside>
                        <nav>
                            <ul>
                                <li><a class="contrast" href="/mon-profil">
                                        <?= IconText("fa-user", "Mon profil") ?>
                                    </a></li>
                                <?php if ($main_user?->family_leader): ?>
                                    <li><button class="outline contrast"
                                            onclick="document.getElementById('familyDrawer').show()">
                                            <?= IconText("fa-users", "Famille") ?>
                                        </button></li>
                                <?php endif ?>
                                <li><a class="contrast destructive" href="/logout">
                                        <?= IconText("fa-power-off", "Déconnexion") ?>
                                    </a></li>

                            </ul>
                        </nav>
                    </aside>
                </sl-dropdown>
            </li>
        </ul>
    </nav>
    <?php if ($main_user?->family_leader): ?>
        <sl-drawer id="familyDrawer" label="Famille" class="drawer-overview">
            <div slot="footer">
                <a role="button" href="/famille/<?= $main_user->family->id ?>" preload><i class="fa fa-gear"></i>
                    Gérer ma famille</a>
            </div>
            <div>
                <p>Ici tu peux prendre le contrôle d'un membre de ta famille !</p>
                <hr style="border-color:#555">
                <aside>
                    <nav class="dropdown-nav">
                        <ul>
                            <?php foreach ($main_user->family->members as $member):
                                if ($member !== $main_user): ?>
                                    <li><a href="/user-control/<?= $member->id ?>" class="contrast"
                                            onclick="document.getElementById('familyDrawer').hide()">
                                            <?= IconText("fa-user", $member->first_name) ?>
                                        </a>
                                    </li>
                                <?php endif;
                            endforeach ?>
                        </ul>
                    </nav>
                </aside>
            </div>
        </sl-drawer>
    <?php endif ?>
<?php
} ?>
