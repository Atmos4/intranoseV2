<?php
restrict_access(Access::$EDIT_USERS);
$v = new Validator();
$url = $v->url("url")->placeholder("Url")->required();
$button_text = $v->text("button_text")->placeholder("Nom")->required();
$description = $v->textarea("description")->placeholder("Description")->required();

if ($v->valid()) {
    $link = new Link($url->value, $button_text->value, $description->value);
    em()->persist($link);
    em()->flush();
    redirect(get_header("HX-Current-Url"));
}
?>
<hr>
<form method="post" hx-post="/liens-utiles/nouveau" class="row">
    <?= $v->render_validation() ?>
    <div class="col-sm-12 col-md-6">
        <?= $url->render() ?>
        <?= $button_text->render() ?>
    </div>
    <div class="col-sm-12 col-md-6">
        <?= $description->render() ?>
        <button type="submit">Valider</button>
    </div>
</form>