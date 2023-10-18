<?php
restrict_access();
$user = em()->find(User::class, get_route_param('user_id'));
if (!$user) {
    force_404("this user doesn't exist");
}

$can_edit_users = check_auth(Access::$EDIT_USERS);
$is_root = check_auth([Permission::ROOT]);

$profile_picture = $user->getPicture();
page($user->first_name . " " . $user->last_name)->css("user_view.css");
?>
<nav id="page-actions">
    <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

    <?php if ($can_edit_users): ?>
        <a href="/user-control/<?= $user->id ?>" class="outline">Contrôler</a>
        <li>
            <details role="list" dir="rtl">
                <summary role="link" aria-haspopup="listbox" class="contrast">Actions</summary>
                <ul role="listbox">
                    <li><a href="/licencies/<?= $user->id ?>/modifier">Modifier</a>
                    </li>
                    <li>
                        <a
                            href="<?= $user->family ? "/famille/{$user->family->id}" : "/licencies/$user->id/creer-famille" ?>">
                            Famille <i class="fa fa-<?= $user->family ? "users" : "plus" ?>"></i>
                        </a>
                    </li>
                    <?php if ($is_root): ?>
                        <li>
                            <a href="/licencies/<?= $user->id ?>/desactiver" class="destructive">
                                Désactiver <i class="fas fa-trash"></i>
                            </a>
                        </li>
                    <?php endif ?>
                </ul>
            </details>
        </li>
    <?php endif ?>
</nav>
<article class="row center">
    <figure class="col">
        <img class="profile-picture" src="<?= $profile_picture ?>">
        <figcaption>
            <?= "$user->first_name $user->last_name" ?>
        </figcaption>
    </figure>
    <div class="col">
        <p>
            <?= $user->nose_email ?>
        </p>
        <p>
            <?= $user->phone ?: "" ?>
        </p>
    </div>
</article>

<?php if (check_auth([Permission::ROOT])): ?>
    <section hx-get="/licencies/<?= $user->id ?>/ovh" hx-swap="innerHTML" hx-trigger="load">
        <div aria-busy="true"></div>
    </section>
<?php endif ?>