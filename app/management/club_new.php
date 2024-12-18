<?php
managementPage("New club");

$v = new Validator(["direct_login" => true]);
$name = $v->text("name")->required()->placeholder("name")->label("Club");
$slug = $v->text("slug")->required()->placeholder("slug");
$fn = $v->text("first_name")->required()->placeholder("first name")->label("First user");
$ln = $v->text("last_name")->required()->placeholder("last name");
$login = $v->text("login")->placeholder("login");
$password = $v->password("password")->placeholder("password");
if ($password->value)
    $password->secure();
$direct_login = $v->switch("direct_login")->label("Login directly");

if ($v->valid())
    do {
        $r = ClubManagementService::createNewClub($name->value, $slug->value);
        if (!$r->success) {
            $v->set_error($r->print());
            break;
        }
        $em = $r->unwrap()->em();
        [$user, $pw] = SeedingService::createTestUser(
            $fn->value,
            $ln->value,
            $em,
            $login->value,
            $password->value
        );
        if (!(new AuthService($em))->tryLogin($user->login, $pw, false, $v))
            break;
        if ($direct_login->value) {
            ClubManagementService::selectClub($slug->value);
            Toast::success("Logged in as $user->login");
            redirect("/evenements");
        }
        $v->set_success("Created club $slug->value with user<br>
        - login: $user->login<br>
        - password: $pw<br>
        Change it asap");
    } while (false);

?>
<form method="post">
    <?= actions()->back("/mgmt")->submit("Submit") ?>
    <section class="row">
        <?= $v ?>
        <div class="col-12 col-md-6">
            <?= $name ?>
            <?= $slug ?>
        </div>
        <div class="col-12 col-md-6">
            <?= $fn ?>
            <?= $ln ?>
        </div>
        <hr>
        <div class="col-12 col-md-6">
            <p>Login and password are optional. If not set, for a user named Jon DOE:
            <ul>
                <li>login will default to <code><?= SeedingService::getFakeLogin("Jon", "DOE") ?></code></li>
                <li>password will default to <code><?= SeedingService::getFakePassword("Jon") ?></code></li>
            </ul>
            </p>
        </div>
        <div class="col-12 col-md-6">
            <?= $login ?>
            <?= $password ?>
            <?= $direct_login ?>
        </div>
    </section>
</form>