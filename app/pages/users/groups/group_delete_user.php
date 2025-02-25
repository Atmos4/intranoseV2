<?php
restrict_access(Access::$EDIT_USERS);

$user = User::getMain();
$group = em()->find(UserGroup::class, get_route_param("group_id"));
if (!$group) {
    force_404("this group does not exist");
}
$member_id = get_route_param("member_id");
if ($member_id) {
    $member = em()->find(User::class, $member_id);
    if (!$member || !$member->groups->contains($group)) {
        force_404("user not member of this group");
    }
}
if (isset($_POST['delete'])) {
    $member->groups->removeElement($group);
    $group->members->removeElement($member);
    em()->persist($member);
    em()->persist($group);
    em()->flush();
    redirect("/groupes/$group->id");
}

page("Supprimer") ?>

<form method="post" class="row center">
    <p>Sûr de vouloir retirer <?= $member->first_name ?> de ce groupe ?
    </p>
    <div class="col-auto">
        <a class="secondary" role="button" href="/group/<?= $group->id ?>">Annuler</a>
    </div>
    <div class="col-auto">
        <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
    </div>
</form>