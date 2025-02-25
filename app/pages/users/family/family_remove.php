<?php
restrict_access();

// TODO: the logic in this page may be a bit complex. If it is, maybe split this into 2 pages.
$user = User::getMain();
$family = em()->find(Family::class, get_route_param("family_id"));
if (!$family) {
    force_404("this family does not exist");
}
$can_delete_family = false;
if (!$user->family_leader || $family != $user->family) {
    // Make sure we only authorize family leaders or admins on this page
    restrict_access(Access::$EDIT_USERS);
    $can_delete_family = true;
}
// Here we want strict parameter if the user doesn't have the right to delete the family
$member_id = get_route_param("member_id", strict: !$can_delete_family);
if ($member_id) {
    $member = em()->find(User::class, $member_id);
    if (!$member || $member->family != $family) {
        force_404("user not member of this family");
    }
}
if (isset($_POST['delete'])) {
    if ($member_id) {
        $member->family = null;
        em()->persist($member);
    } else {
        foreach ($family->members as $member) {
            $member->family = null;
            em()->persist($member);
        }
        em()->remove($family);
    }
    em()->flush();
    redirect($member_id ? "/famille/$family->id" : "/familles");
}

page("Supprimer") ?>

<form method="post" class="row center">
    <p>Sûr de vouloir
        <?= $member_id ? "retirer $member->first_name de cette famille" : "supprimer cette famille" ?>?
    </p>
    <div class="col-auto">
        <a class="secondary" role="button" href="/famille/<?= $family->id ?>">Annuler</a>
    </div>
    <div class="col-auto">
        <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
    </div>
</form>