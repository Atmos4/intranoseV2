<?php
restrict_access();
$user = User::getMain();
$family = em()->find(Family::class, get_route_param("family_id"));
if (!$family) {
    force_404("this family does not exist");
}
if (!$user->family_leader || $family != $user->family) {
    restrict_access(Access::$EDIT_USERS);
}
$member = em()->find(User::class, get_route_param("member_id"));
if (!$member || $member->family != $family) {
    force_404("user not member of this family");
}
$member->family_leader = !$member->family_leader;
em()->persist($member);
em()->flush();
redirect("/famille/$family->id");