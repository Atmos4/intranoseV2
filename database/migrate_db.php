<?php
restrict_dev();

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

$database = new PDO(
    "mysql:dbname=" . env("OLD_DB_NAME") . ";host=" . env("OLD_DB_HOST") . ";charset=utf8mb4",
    env("OLD_DB_USER"),
    env("OLD_DB_PASSWORD"),
);
$users = $database->query('SELECT * FROM licencies WHERE valid=1 and invisible=0')->fetchAll();

$v = new Validator(action: "migrate");

if ($v->valid()) {
    foreach ($users as $user) {
        $newUser = new User();
        $newUser->last_name = $user['nom'];
        $newUser->first_name = $user['prenom'];
        $newUser->login = $user['login'];
        $newUser->password = password_hash(strtolower($user["prenom"]), PASSWORD_DEFAULT);
        $newUser->nose_email = $user['email'];
        $newUser->real_email = $user['realmail'];
        $newUser->phone = $user['telport'] ?? $user['tel'];
        $newUser->permission = map_permission($user['perm']);
        $newUser->gender = map_gender($user['sexe']);
        $newUser->birthdate = date_create($user['ddn']);
        em()->persist($newUser);
    }
    em()->flush();
}

$count = count($users);

page("Migration")->disableNav()
    ?>

<?php if ($v->valid()):
    echo "Pas assez rapide! MI-GRA-TIOOOOON!!! $count users migrated";
else: ?>
    <form method="post">
        <?= $v->render_validation() ?>
        <p>Sûr de vouloir migrer
            <?= $count ?> utilisateurs
        </p>
        <button>UI</button>
    </form>
<?php endif ?>