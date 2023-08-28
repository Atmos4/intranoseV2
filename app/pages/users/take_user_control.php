<?php
restrict_access();
$user = User::getMain();
$controlled_user = User::get(get_route_param('user_id'));
if (!$controlled_user) {
    force_404("this user doesn't exist");
}
if ($controlled_user->family != $user->family) {
    restrict_access(Access::$EDIT_USERS);
}
$_SESSION['controlled_user_id'] = $controlled_user->id;
redirect("/evenements");