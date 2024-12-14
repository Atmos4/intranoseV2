<?php
$clubs = ClubManagementService::listClubs();
restrict(!env("PRODUCTION"));
page("Select club")->disableNav();
$v = new Validator(action: "select-club");
if ($v->valid()) {
    ClubManagementService::selectClub($v->value("club"));
    redirect("/", true);
}
?>
<article class="notice">Club selection is a work in progress</article>
<aside>
    <nav>
        <ul>
            <?php foreach (ClubManagementService::listClubs() as $c): ?>
                <li><button role="link" hx-post hx-target="body" <?= $v->hx_action(["club" => $c]) ?>>
                        <?= $c ?>
                    </button></li>
            <?php endforeach ?>
        </ul>
    </nav>
</aside>