<?php
$group_id = get_route_param("group_id", false);

if ($group_id) {
    $g = em()->find(UserGroup::class, $group_id);
    if ($g == null) {
        force_404("Error: the group with id $group_id does not exist");
    }
    $group_form_values = [
        'name' => $g->name,
        'color' => $g->color->value,
    ];
} else {
    $g = new UserGroup();
}

$color_options = [];
foreach (ThemeColor::cases() as $color) {
    $color_options[$color->value] = $color->translate();
}

$v = new Validator($group_form_values ?? [], action: "create_group");
$name = $v->text("name")->label("Nom")->placeholder()->required();
$theme_color = $v->select('color')->options($color_options, )->label('Couleur du groupe');
if ($v->valid()) {
    $g->name = $name->value;
    $g->color = ThemeColor::from($theme_color->value);
    em()->persist($g);
    em()->flush();
    Toast::create("Groupe mis à jour");
    redirect("/groupes/" . $g->id);
}
?>
<form method="post">
    <?= $v->render_validation() ?>
    <div class="row">
        <?= $name->render() ?>
        <?= $theme_color->render() ?>
        <div class="col-auto">
            <button type="submit"><?= $group_id ? "Modifier" : "Créer" ?></button>
        </div>
    </div>
</form>