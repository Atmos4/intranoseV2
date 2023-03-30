<?php
if (!env('developement')) {
    force_404("Not in dev environement");
}

$user = em()->find(User::class, get_route_param("user_id"));
if (!$user) {
    force_404("User not found");
}
$user->set_passoword($user->first_name);
em()->persist($user);
em()->flush();
echo "Password for $user->first_name has been reset";