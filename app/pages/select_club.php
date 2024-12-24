<?php
$clubs = ClubManagementService::listClubs();
restrict(!env("PRODUCTION"));
page("Select club")->css("login.css")->disableNav();
$v = new Validator(action: "select-club");
if ($v->valid()) {
    ClubManagementService::selectClub($v->value("club"));
    redirect("/", true);
}
$clubs = ClubManagementService::listClubs();
?>
<article class="notice">Club selection is a work in progress</article>
<?php if (!$clubs): ?>
    <p>No clubs available</p>
    <?php return;
endif ?>
<aside>
    <nav>
        <ul>
            <?php foreach ($clubs as $c): ?>
                <li><button role="link" hx-post hx-target="body" <?= $v->hx_action(["club" => $c]) ?>>
                        <?= $c ?>
                    </button></li>
            <?php endforeach ?>
        </ul>
    </nav>
</aside>