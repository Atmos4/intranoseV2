<?php
restrict_access(Access::$ADD_EVENTS);
$s = ClubManagementService::create();
$db = $s->db;
$club = $s->getClub();

$theme_form_values = [
    'theme_color' => $club->themeColor->value,
];

/* THEME */

$color_options = [];
foreach (ThemeColor::cases() as $color) {
    $color_options[$color->value] = $color->translate();
}
$v_theme = new Validator($theme_form_values, action: 'theme_form');
$theme_color = $v_theme->select('theme_color')->options($color_options)->label('Couleur du theme')->help("Changez la couleur de thème de votre club ici !");


if ($v_theme->valid()) {
    $club->themeColor = ThemeColor::from($theme_color->value);
    $db->em()->persist($club);
    $db->em()->flush();
    Toast::create("Thème mis à jour");
}

/* FEATURES */

$club_features = FeatureService::listClub(service: $s);
$v_features = new Validator(action: "features");
$feature_options = [];
foreach ($club_features as $f) {
    $feature_options[$f->featureName] = Feature::from($f->featureName)->translate();
}
$features = $v_features->select("add_new")->options($feature_options)->label("Nouvelle fonctionnalité")->required();

if ($v_features->valid()) {
    $newFeature = $club_features[$features->value];
    $newFeature->enabled = true;
    $db->em()->persist($newFeature);
    $db->em()->flush();
    Toast::success("Fonctionnalité ajoutée");
    reload();
}

$v_removeFeature = new Validator(action: "remove_feature");
if ($v_removeFeature->valid() && isset($_POST['remove_name']) && isset($club_features[$_POST['remove_name']])) {
    $newFeature = $club_features[$_POST['remove_name']];
    $newFeature->enabled = false;
    $db->em()->persist($newFeature);
    $db->em()->flush();
    Toast::error("Fonctionnalité retirée");
    reload();
}

page("Paramètres du club")->enableHelp();
?>
<h2>Thème</h2>
<form method="post" hx-boost="false">
    <?= $v_theme->render_validation() ?>
    <?= $theme_color->render() ?>
    <input type="submit" class="outline" name="submitTheme" value="Mettre à jour le thème">
</form>
<section>
    <h3>Fonctionnalités du club</h3>
    <ul>
        <?php if (!$club_features): ?>
            Pas de fonctionnalités disponibles pour ce club, contactez les développeurs pour accéder au nouvelles
            fonctionnalités 🚀
        <?php else: ?>
            <?php foreach (array_filter($club_features, fn($f) => $f->enabled) as $club_feature): ?>
                <li style="display:flex;align-items:center;gap:1rem;padding: 0.5rem">
                    <?= Feature::from($club_feature->featureName)->translate() ?>
                    <form method="post">
                        <?= $v_removeFeature ?>
                        <input type="hidden" name="remove_name" value="<?= $club_feature->featureName ?>">
                        <button class="destructive">Retirer</button>
                    </form>
                </li>
            <?php endforeach ?>
        </ul>
        <form method="post">
            <?= $v_features ?>
            <?= $features ?>
            <button>Ajouter</button>
        </form>
    <?php endif ?>
</section>