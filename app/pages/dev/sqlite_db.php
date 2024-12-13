<?php
// NEVER CHANGE THIS PLEASE - FOR DEV ONLY
restrict_dev();
restrict_access([Permission::ROOT]);
restrict(DB::getInstance()->isSqlite());

$v = new Validator(action: "bidule");
$input = $v->text("sql")->placeholder("SQL");
$values = $v->text("values")->placeholder("Values");
$result = null;

if ($v->valid()) {
    try {
        $rows = em()->getConnection()->fetchAllAssociative($input->value, $values->value ? explode(",", $values->value) : []);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
page("SQLITE");
?>
<form method="post">
    <?= $v ?>
    <?= $input ?>
    <?= $values ?>
    <button>Execute</button>
</form>
<?php if ($input->value): ?>
    <section>Query:
        <?= $input->value ?>
    </section>
    <section>
        Data:
        <pre><?= print_r($rows, true) ?></pre>
    </section>
<?php endif ?>