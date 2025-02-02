<?php
restrict_access(Access::$ADD_EVENTS);
$club = ClubManagementService::getSelectedClub();

$google_form_values = [
    'google_id' => $club->google_calendar_id,
];

$theme_form_values = [
    'theme_color' => $club->themeColor->value,
];

$credentials_help = "<ul>
    <li>Allez sur <a href='https://console.cloud.google.com'>https://console.cloud.google.com</a> </li>
    <li>Créez un projet</li>
    <li>Créez un service account</li>
    <li>Téléchargez le fichier de credentials au format .json</li>
    <li>Autoriser l'accès au calendrier cible pour le service account créé</li>
</ul>";

$v_google_calendar = new Validator($google_form_values, action: 'google_calendar_form');
$google_id = $v_google_calendar->text("google_id")->label("ID du calendrier")->placeholder()->help("Remplissez l'id du calendrier lié au service account créé.");
$google_credentials = $v_google_calendar->upload("credentials")->max_size(2 * 1024 * 1024)->label("Fichier de credentials du service account")->help($credentials_help);

/* THEME */

$v_theme = new Validator($theme_form_values, action: 'theme_form');
$theme_color = $v_theme->select('theme_color')->options(array_column(ThemeColor::cases(), 'value', 'name'))->label('Couleur du theme')->help("Changez la couleur de thème de votre club ici !");


if ($v_google_calendar->valid()) {
    $club->google_calendar_id = $google_id->value;
    if (GoogleCalendarService::clearCredentialFolder()) {
        if ($path = $google_credentials->save_file(Path::credentials())) {
            $club->google_credential_path = $path;
            em()->flush();
        } else {
            $v_google_calendar->set_error("Erreur lors de l'enregistrement des credentials");
        }
    } else {
        $v_google_calendar->set_error("Erreur lors du nettoyage du dossier des credentials");
    }
    em()->persist($club);
    em()->flush();
    Toast::create("Calendrier mis à jour");
}

if ($v_theme->valid()) {
    $club->themeColor = ThemeColor::from($theme_color->value);
    em()->persist($club);
    em()->flush();
    Toast::create("Thème mis à jour");
}

/* FEATURES */

$club_features = FeatureService::list_club();
$v_features = new Validator(action: "features");
$feature_options = [];
foreach ($club_features as $f) {
    $feature_options[$f->featureName] = $f->featureName;
}
$features = $v_features->select("add_new")->options($feature_options)->label("Nouvelle fonctionnalité")->required();

if ($v_features->valid()) {
    $newFeature = $club_features[$features->value];
    $newFeature->enabled = true;
    em()->persist($newFeature);
    em()->flush();
    Toast::success("Fonctionnalité ajoutée");
    reload();
}

$v_removeFeature = new Validator(action: "remove_feature");
if ($v_removeFeature->valid() && isset($_POST['remove_name']) && isset($club_features[$_POST['remove_name']])) {
    $newFeature = $club_features[$_POST['remove_name']];
    $newFeature->enabled = false;
    em()->persist($newFeature);
    em()->flush();
    Toast::error("Fonctionnalité retirée");
    reload();
}

page("Paramètres du club")->enableHelp();
?>
<h2>Thème</h2>
<form method="post">
    <?= $v_theme->render_validation() ?>
    <?= $theme_color->render() ?>
    <input type="submit" class="outline" name="submitTheme" value="Mettre à jour le thème">
</form>
<?php if (Feature::GoogleCalendar->on()): ?>
    <h2 id="google">Google Calendar</h2>
    <form method="post" enctype="multipart/form-data">
        <?= $v_google_calendar->render_validation() ?>
        <?= $google_credentials->render() ?>
        <?= $google_id->render() ?>
        <input type="submit" class="outline" name="submitGoogle" value="Mettre à jour le calendrier">
    </form>
<?php endif ?>
<section>
    <h3>Fonctionnalités du club</h3>
    <ul>
        <?php if (!$club_features): ?>
            Pas de fonctionnalités disponibles pour ce club, contactez les développeurs pour accéder au nouvelles
            fonctionnalités 🚀
        <?php else: ?>
            <?php foreach (array_filter($club_features, fn($f) => $f->enabled) as $club_feature): ?>
                <li style="display:flex;align-items:center;gap:1rem;padding: 0.5rem">
                    <?= $club_feature->featureName ?>
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