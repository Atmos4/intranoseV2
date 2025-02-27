<?php
$v = new Validator(action: "toast");
$success = $v->number("success")->placeholder("success count");
$error = $v->number("error")->placeholder("error count");
$info = $v->number("info")->placeholder("info count");
$warning = $v->number("warning")->placeholder("warning count");
$content = $v->text("content")->placeholder("content");
if ($v->valid()) {
    while ($success->value) {
        Toast::success($_POST['content']);
        $success->value--;
    }
    while ($error->value) {
        Toast::error($_POST['content']);
        $error->value--;
    }
    while ($info->value) {
        Toast::info($_POST['content']);
        $info->value--;
    }
    while ($warning->value) {
        Toast::warning($_POST['content']);
        $warning->value--;
    }
}
page("Toasts");
?>
<form method="post">
    <?= $v->render_validation() ?>
    <?= $success->render() ?>
    <?= $error->render() ?>
    <?= $info->render() ?>
    <?= $warning->render() ?>
    <?= $content->render() ?>
    <button>Add toasts</button>
</form>