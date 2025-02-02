<?php
if (!ClubManagementService::isLoggedIn()) {
    redirect("/mgmt/login");
}
managementPage("Clubs");
/* Workaround to handle the case where a club is selected, so that the color works in layout.php */
if ($club_slug = ClubManagementService::getSelectedClubSlug()) {
    DB::setupForClub($club_slug);
}
$clubs = ClubManagementService::listClubs();
?>
<?= actions()->link("/mgmt/new-club", "Add", "fa-plus")->link("/mgmt/logout", "Logout", attributes: ["class" => "destructive"]) ?>

<?= import(__DIR__ . "/components/selected_club_notice.php")(env("SELECTED_CLUB")) ?>

<?php if (!$clubs): ?>
    <p>No club yet</p>
<?php endif ?>
<ul>
    <?php foreach ($clubs as $c): ?>
        <li>
            <a href="/mgmt/view/<?= $c ?>">
                <?= $c ?>
            </a>
        </li>
    <?php endforeach ?>
</ul>