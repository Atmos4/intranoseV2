<?php
restrict_dev();

$user = em()->find(User::class, get_route_param("user_id"));
if (!$user) {
    force_404("User not found");
}
$user->set_password(strtolower($user->first_name));
em()->persist($user);
em()->flush();
echo "Password for $user->first_name has been reset";