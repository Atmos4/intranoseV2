<?php
restrict_access();
$user = em()->find(User::class, get_route_param('user_id'));
if (!$user) {
    force_404("this user doesn't exist");
}

$can_edit_users = check_auth(Access::$EDIT_USERS);
$is_root = check_auth([Permission::ROOT]);

$result_image = glob("assets/images/profile/" . $user->id . ".*");

$profile_picture = (count($result_image) > 0) ?
    "/" . $result_image[0]
    : "/assets/images/profile/none.jpg";
page($user->first_name . " " . $user->last_name)->css("user_view.css");
?>
<nav id="page-actions">
    <a href=<?= $user->active ? "/licencies" : "/licencies/reactivate" ?> class="secondary"><i
            class="fas fa-caret-left"></i> Retour</a>

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
                            <a href="/licencies/<?= $user->id ?>/supprimer" class="destructive">
                                Désactiver <i class="fas fa-trash"></i>
                            </a>
                        </li>
                    <?php endif ?>
                </ul>
            </details>
        </li>
    <?php endif ?>
</nav>
<article class="grid center">
    <figure>
        <img class="profile-picture" src="<?= $profile_picture ?>">
    </figure>
    <table class="infos-table">
        <tr>
            <td>Email</td>
            <td>
                <?= $user->nose_email ?>
            </td>
        </tr>
        <tr>
            <td>Téléphone</td>
            <td>
                <?= $user->phone ?: "" ?>
            </td>
        </tr>
    </table>
</article>