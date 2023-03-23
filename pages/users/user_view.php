<?php
restrict_access();
$user = em()->find(User::class, get_route_param('user_id'));
if (!$user) {
    force_404("this user doesn't exist");
}
$profile_picture = (file_exists("images/profile/" . $user->id . ".jpg")) ?
    "/images/profile/" . $user->id . ".jpg"
    : "/images/profile/none.jpg";
page($user->first_name . " " . $user->last_name, "user_view.css");
?>
<div id="page-actions">
    <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
</div>
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
            <td>Adresse</td>
            <td>
                <?= join(
                    "<br/>",
                    [
                        $user->address,
                        "$user->postal_code $user->city"
                    ]
                ) ?>
            </td>
        </tr>
        <tr>
            <td>Téléphone</td>
            <td>
                <?= $user->phone ?: "" ?>
            </td>
        </tr>
        <tr>
            <td>Licence</td>
            <td>
                <?= $user->licence ?>
            </td>
        </tr>
        <tr>
            <td>Sport Ident</td>
            <td>
                <?= $user->sportident ?: "" ?>
            </td>
        </tr>
    </table>
</article>