<?php
managementPage("New club");

$v = new Validator;
$name = $v->text("name")->required()->placeholder("name");
$slug = $v->text("slug")->required()->placeholder("slug");

if ($v->valid()) {
    $r = ClubManagementService::createNewClub($name->value, $slug->value);
    Toast::fromResult($r);
    $r->success && redirect("/mgmt");
}
?>
<?= actions()->back("/mgmt") ?>
<form method="post">
    <?= $v ?>
    <?= $name ?>
    <?= $slug ?>
    <button>Submit</button>
</form>