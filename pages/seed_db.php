<?php

if (!env('developement')) {
    force_404("Not in dev environement: can't seed database");
}

function map_permission(string $p): Permission
{
    switch ($p) {
        case 'STAFF':
            return Permission::STAFF;
        case 'COACHSTAFF':
            return Permission::COACHSTAFF;
        case 'COACH':
            return Permission::COACH;
        case 'ROOT':
            return Permission::ROOT;
        case 'GUEST':
            return Permission::GUEST;
        case 'USER':
        default:
            return Permission::USER;
    }
}

function map_gender(string $g): Gender
{
    if ($g == 'H') {
        return Gender::M;
    }
    return Gender::W;
}

$users = fetch('SELECT * FROM licencies WHERE valid=1 and invisible=0');
foreach ($users as $user) {
    $newUser = new User();
    $newUser->last_name = $user['nom'];
    $newUser->first_name = $user['prenom'];
    $newUser->login = $user['login'];
    $newUser->password = password_hash($user["prenom"], PASSWORD_DEFAULT);
    $newUser->address = $user['adresse1'];
    $newUser->postal_code = $user['cp'];
    $newUser->city = $user['ville'];
    $newUser->sportident = $user['sportident'];
    $newUser->licence = $user['num_lic'];
    $newUser->nose_email = $user['email'];
    $newUser->real_email = $user['realmail'];
    $newUser->phone = preg_replace("/[^0-9+]/", "", $user['telport']);
    $newUser->permission = map_permission($user['perm']);
    $newUser->gender = map_gender($user['sexe']);
    $newUser->birthdate = date_create($user['ddn']);
    em()->persist($newUser);
}

em()->flush();
$count = count($users);

echo "Inserted $count users";