<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param('event_id');
$team_group_id = get_route_param('team_group_id');
$event = em()->find(Event::class, $event_id);

$team_group = em()->find(TeamGroup::class, $team_group_id);
if (!$team_group || $team_group->event->id !== $event->id) {
    Toast::error("Groupe d'équipes introuvable");
    redirect("/evenements/$event_id?tab=teams");
}

$v = new Validator();

if ($v->valid()) {
    $team_group->published = !$team_group->published;
    em()->persist($team_group);
    em()->flush();
    $team_group->published ? Toast::success("Groupe publié") : Toast::success("Groupe retiré");
    redirect("/evenements/$event_id/groupe-equipes/$team_group_id");
}

page(($team_group->published ? "Retirer" : "Publier") . " - " . ($team_group->name ?: "Groupe #$team_group_id"));
?>

<?= actions()->back("/evenements/$event_id/groupe-equipes/$team_group_id") ?>

<div class="container">
    <article>
        <form method="post" class="center">
            <?= $v->render_validation() ?>
            <p>
                Sûr de vouloir <?= $team_group->published ? "retirer" : "publier" ?> le groupe d'équipe
                <strong><?= htmlspecialchars($team_group->name ?: "Groupe #$team_group_id") ?></strong> ?
            </p>
            <p>
                <?php if ($team_group->published): ?>
                    Les équipes ne seront plus visibles par les membres.
                <?php else: ?>
                    Les équipes seront visibles par les membres.
                <?php endif ?>
            </p>
            <div class="row" style="justify-content: center;">
                <div class="col-auto">
                    <a class="secondary" role="button"
                        href="/evenements/<?= $event_id ?>/groupe-equipes/<?= $team_group_id ?>">Annuler</a>
                </div>
                <div class="col-auto">
                    <button type="submit" name="publish" value="true" <?= $team_group->published ? "class='contrast'" : "" ?>>
                        <i class="fa <?= $team_group->published ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                        <?= $team_group->published ? "Retirer" : "Publier" ?>
                    </button>
                </div>
            </div>
        </form>
    </article>
</div>