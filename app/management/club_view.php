<?php
managementPage("View club");
$slug = Router::getParameter("slug", pattern: '/[\w-]+/');
$s = ClubManagementService::fromSlug($slug) ?? force_404("club not found");
$club = $s->getClub();

$backupService = new BackupService(dbPath: $s->db->sqlitePath);

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
            <h3><i>Danger zone</i></h3>
            <button class="destructive" hx-post hx-confirm="Are you sure you want to delete the club?"
                <?= $v_delete->hx_action() ?>>Delete</button>
        </form>
    </sl-tab-panel>
    <sl-tab-panel name="backups">
        <form hx-post="/mgmt/view/<?= $slug ?>/backups" hx-trigger="load,submit" hx-target="this">
        </form>
    </sl-tab-panel>
</sl-tab-group>