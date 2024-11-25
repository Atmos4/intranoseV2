<?php
// NEVER CHANGE THIS PLEASE - FOR DEV ONLY
restrict_dev();
restrict_access([Permission::ROOT]);
restrict(DB::getInstance()->isSqlite());

$v = new Validator();
$input = $v->text("sql")->placeholder("SQL");
$values = $v->text("values")->placeholder("Values");
$result = null;

$db_name = env("SQLITE_DB_NAME");
try {
    $pdo = new PDO('sqlite:.sqlite/' . env("SQLITE_DB_NAME"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($v->valid()) {
    try {
        $sql = $input->value;
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($values->value ? explode(",", $values->value) : null);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

page("SQLITE");

?>
<p>Connected to
    <?= $db_name ?>
</p>
<form method="post">
    <?= $v ?>
    <?= $input ?>
    <?= $values ?>
    <button>Execute</button>
</form>
<?php if ($result !== null): ?>
    <section>Query:
        <?= $input->value ?>
    </section>
    <section>Result:
        <?= $result ? "success" : "error" ?>
    </section>

    <section>
        Data:
        <pre><?= print_r($rows, true) ?></pre>
    </section>
<?php endif ?>