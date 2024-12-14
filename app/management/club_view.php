<?php
managementPage("View club");
$slug = Router::getParameter("slug", pattern: '/[\w-]+/');
$s = ClubManagementService::fromSlug($slug) ?? force_404("club not found");
$club = $s->getClub();

// Delete club - be careful with this
$v_delete = new Validator(action: "delete");
if ($v_delete->valid()) {
    if ($s->deleteClub())
        redirect("/mgmt");
    else
        Toast::error("Could not delete club");
}

$v = new Validator($club->toForm());
$name = $v->text("name")->label("Name");

if ($v->valid()) {
    $r = $s->updateClub($club, $name->value);
    Toast::fromResult($r);
    $r->success && redirect("/mgmt/view/$club->slug");
}
?>
<?= actions()->back("/mgmt") ?>
<sl-tab-group>
    <sl-tab slot="nav" panel="general">General</sl-tab>
    <sl-tab slot="nav" panel="logs">Logs</sl-tab>

    <sl-tab-panel name="general">
        <form method="post">
            <?= $v ?>
            <?php if (!$club->name): ?>
                <article class="notice error">Please fill in the club name</article>
            <?php endif ?>
            <?= $name ?>
            <button>Update</button>
            <br><br>
            <h3><i>Danger zone</i></h3>
            <button class="destructive" hx-post hx-confirm="Are you sure you want to delete the club?"
                <?= $v_delete->hx_action() ?>>Delete</button>
        </form>
    </sl-tab-panel>
    <sl-tab-panel name="logs">Logs</sl-tab-panel>
</sl-tab-group>