<?php
managementPage("MGMT - login");

$v = new Validator;
$password = $v->password("mgmt_pw")->required();
if ($v->valid()) {
    if (ClubManagementService::login($password->value)) {
        Toast::success("Logged in");
        redirect("/mgmt");
    }
    $password->set_error("wrong password");
}
?>
<form method="post">
    <?= $v ?>
    <?= $password ?>
    <button>Login</button>
</form>