<?php
$groups = GroupService::listGroups();
$colorList = ThemeColor::colorsList();
page("Groupes")->css("group_list.css"); ?>
<?= actions()->back("/licencies")->link("/groupes/nouveau", "Nouveau groupe", "fas fa-plus") ?>
<table>
    <?php foreach ($groups as $group): ?>
        <article class="group-article" hx-trigger="click,keyup[key=='Enter'||key==' ']" hx-get="/groupes/<?= $group->id ?>"
            hx-target="body" hx-push-url="true" tabindex=0>
            <div class="grid">
                <div class="group-dot" style="background-color:<?= $colorList[$group->color->value] ?>"></div>
                <div class="title">
                    <b>
                        <?= $group->name ?>
                    </b>
                </div>
            </div>
        </article>
    <?php endforeach;
    if (!$groups): ?>
        <p class="center">Pas encore de groupes 😲</p>
    <?php endif ?>
</table>