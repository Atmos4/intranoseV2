<?php
managementPage("View club");
$slug = Router::getParameter("slug", pattern: '/[\w-]+/');
DB::setupForClub($slug);
$s = ClubManagementService::fromSlug($slug) ?? force_404("club not found");
$club = ClubManagementService::getSelectedClub();

$backupService = new BackupService(dbPath: DB::getInstance()->sqlitePath);

$v_backup = new Validator(action: "create_backup");
if ($v_backup->valid()) {
    $backupService->createBackup();
}

// Delete club - be careful with this
$v_delete = new Validator(action: "delete");
if ($v_delete->valid()) {
    if ($s->deleteClub())
        redirect("/mgmt");
    else
        Toast::error("Could not delete club");
}

$colorList = ThemeColor::colorsList();

$v = new Validator($club->toForm());
$color = $v->select("color")->options(array_column(ThemeColor::cases(), 'value', 'name'))->label("Couleur de thème");
$name = $v->text("name")->label("Name");

if ($v->valid()) {
    $r = $s->updateClub($club, $name->value, $color->value);
    Toast::fromResult($r);
    $r->success && redirect("/mgmt/view/$club->slug");
}

$club_features = FeatureService::list_club($slug);
$v_features = new Validator(action: "features");
$feature_options = [];
foreach (Feature::cases() as $f) {
    $feature_options[$f->value] = $f->value;
}
$features = $v_features->select("add_new")->options($feature_options)->label("New feature")->required();

if ($v_features->valid()) {
    $newFeature = $club_features[$features->value] ?? new ClubFeature($club, Feature::from($features->value));
    em()->persist($newFeature);
    em()->flush();
    Toast::success("Feature added");
    reload();
}

$v_removeFeature = new Validator(action: "remove_feature");
if ($v_removeFeature->valid() && isset($_POST['remove_name']) && isset($club_features[$_POST['remove_name']])) {
    em()->remove($club_features[$_POST['remove_name']]);
    em()->flush();
    Toast::error("Feature removed");
    reload();
}

?>
<?= actions()->back("/mgmt") ?>
<sl-tab-group>
    <sl-tab slot="nav" panel="general">General</sl-tab>
    <sl-tab slot="nav" panel="backups">Backups</sl-tab>

    <sl-tab-panel name="general">
        <form method="post">
            <?= $v ?>
            <?php if (!$club->name): ?>
                <article class="notice error">Please fill in the club name</article>
            <?php endif ?>
            <?= $name ?>
            <label for="color">Couleurs disponibles</label>
            <div class="color-picker">
                <?php foreach ($colorList as $key => $colorItem): ?>
                    <sl-tooltip content="<?= $key ?>">
                        <div class="color-dot" style="background-color:<?= $colorItem ?>">
                        </div>
                    </sl-tooltip>
                <?php endforeach ?>
            </div>
            <?= $color ?>
            <button>Update</button>
            <br><br>
        </form>
        <section>
            <h3>Features</h3>
            <ul>
                <?php foreach ($club_features as $club_feature): ?>
                    <li style="display:flex;align-items:center;gap:1rem;padding: 0.5rem">
                        <?= $club_feature->featureName ?>
                        <form method="post">
                            <?= $v_removeFeature ?>
                            <input type="hidden" name="remove_name" value="<?= $club_feature->featureName ?>">
                            <button class="destructive">Remove</button>
                        </form>
                    </li>
                <?php endforeach ?>
            </ul>
            <form method="post">
                <?= $v_features ?>
                <?= $features ?>
                <button>Add</button>
            </form>
        </section>
        <h3><i>Danger zone</i></h3>
        <form method="post">
            <button class="destructive" hx-post hx-confirm="Are you sure you want to delete the club?"
                <?= $v_delete->hx_action() ?>>Delete club</button>
        </form>
    </sl-tab-panel>
    <sl-tab-panel name="backups">
        <form hx-post="/mgmt/view/<?= $slug ?>/backups" hx-trigger="load,submit" hx-target="this">
        </form>
    </sl-tab-panel>
</sl-tab-group>