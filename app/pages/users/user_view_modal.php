<?php
restrict_access();

$user = em()->find(User::class, Component::prop('user_id') ?? get_route_param('user_id'));
if (!$user) {
    force_404("this user doesn't exist");
}

$can_edit_users = check_auth(Access::$EDIT_USERS) && $user != User::getMain();
$is_root = check_auth([Permission::ROOT]);

$profile_picture = $user->getPicture();

// HTMX - replace url
$hxUrl = explode("?", get_header("HX-Current-URL"))[0];
header("HX-Replace-Url: $hxUrl?user=$user->id");
// HTMX - show user modal
if (!Component::mounted()) {
    header("HX-Trigger-After-Settle: showModal");
}
?>
<article>
    <header>
        <button class="close secondary" role="link" onclick="
            let modal = htmx.closest(this, 'dialog')
            let article = htmx.closest(this, 'article')
            modal.classList.add('closing')
            modal.addEventListener('animationend', () => {
                modal.close()
                // reset state
                modal.classList.remove('closing')
                history.replaceState({htmx:true}, '', '<?= $hxUrl ?>')
            }, { once: true })
            ">
        </button>
        <b>
            <?= "$user->first_name $user->last_name" ?>
        </b>
    </header>

    <div class="row center">
        <figure class="col">
            <img class="profile-picture" src="<?= $profile_picture ?>">
        </figure>
        <p>
            <?= $user->nose_email ?>
        </p>
        <p>
            <?= $user->phone ?: "" ?>
        </p>
    </div>

    <?php if ($can_edit_users): ?>
        <footer>
            <nav>
                <li><a href="/user-control/<?= $user->id ?>" class="outline">Contrôler</a></li>
                <li>
                    <details role="list" dir="rtl">
                        <summary role="link" aria-haspopup="listbox" class="contrast">Actions</summary>
                        <ul role="listbox" data-placement="top">
                            <li><a href="/licencies/<?= $user->id ?>/modifier">Modifier</a>
                            </li>
                            <li>
                                <a
                                    href="<?= $user->family ? "/famille/{$user->family->id}" : "/licencies/$user->id/creer-famille" ?>">
                                    <i class="fa fa-<?= $user->family ? "users" : "plus" ?>"></i> Famille
                                </a>
                            </li>
                            <?php if ($is_root): ?>
                                <li>
                                    <a href="/licencies/<?= $user->id ?>/desactiver" class="destructive">
                                        <i class="fas fa-trash"></i> Désactiver
                                    </a>
                                </li>
                            <?php endif ?>
                        </ul>
                    </details>
                </li>
            </nav>
        <?php endif ?>
</article>