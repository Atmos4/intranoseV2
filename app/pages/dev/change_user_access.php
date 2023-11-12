<?php
restrict_dev();

$user = User::getMain();

// FORM
$v = new Validator(["access" => $user->permission->value]);
$select = $v->select("access")->options(array_column(Permission::cases(), 'value', 'name'))->label("Permission");
if ($v->valid()) {
    $user->permission = Permission::from($select->value);
    $_SESSION['user_permission'] = $user->permission;
    em()->flush();
    Toast::success("Changement effectué");
}

page("Change access");
?>
<form method="post">
    <p>User:
        <code><?= $user->login ?></code>
    </p>
    <?= $v->render_validation() ?>
    <?= $select->render() ?>
    <button>Submit</button>
</form>