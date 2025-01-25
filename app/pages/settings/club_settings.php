<?php
restrict_access(Access::$ADD_EVENTS);
$club = em()
    ->createQuery("SELECT c from Club c WHERE c.slug = :slug")
    ->setParameters(["slug" => ClubManagementService::getSelectedClubSlug()])
    ->getResult()[0];

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

$v_theme = new Validator($theme_form_values, action: 'theme_form');
$theme_color = $v_theme->select('theme_color')->options(array_column(ThemeColor::cases(), 'value', 'name'))->label('Couleur du theme');


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