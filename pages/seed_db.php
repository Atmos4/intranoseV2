<?php

if (!env('developement')) {
    force_404("Not in dev environement: can't seed database");
}

$newUser = new User();
$newUser->last_name = "White";
$newUser->first_name = "Walter";
$newUser->login = "white_w";
$newUser->password = password_hash("heisenberg", PASSWORD_DEFAULT);
$newUser->sportident = 996666;
$newUser->licence = 1;
$newUser->nose_email = "walter.white@nose42.fr";
$newUser->real_email = "walter.white@gmail.com";
$newUser->phone = "0612345678";
$newUser->permission = Permission::ROOT;
$newUser->gender = Gender::M;
$newUser->birthdate = date_create("1958-09-07");
$new_user->active = 1;
em()->persist($newUser);

em()->flush();

echo "Created user $user->first_name $user->last_name";
echo "Login: $user->login";
echo "Password: $user->password";