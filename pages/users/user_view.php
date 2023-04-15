<?php
restrict_access();
$user = em()->find(User::class, get_route_param('user_id'));
if (!$user) {
    force_404("this user doesn't exist");
}

$can_edit_users = check_auth(Access::$EDIT_USERS);

$v = new Validator(action: "control");
if ($v->valid() && $can_edit_users) {
    $_SESSION['controlled_user_id'] = $user->id;
    redirect("/evenements");
}

$profile_picture = (file_exists("images/profile/" . $user->id . ".jpg")) ?
    "/images/profile/" . $user->id . ".jpg"
    : "/images/profile/none.jpg";
page($user->first_name . " " . $user->last_name)->css("user_view.css");
?>
<form method="post">
    <?= $v->render_validation() ?>
    <nav id="page-actions">
        <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

        <?php if ($can_edit_users): ?>
            <div>
                <button type="submit" class="outline">Contrôler</button>
            </div>
            <a href="/licencies/<?= $user->id ?>/modifier" class="contrast">Modifier</a>
        <?php endif ?>
    </nav>
    <article class="grid center">
        <figure>
            <img src="<?= $profile_picture ?>">
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
</form>