<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param('event_id');
$pool_id = get_route_param('pool_id');
$event = em()->find(Event::class, $event_id);

$team_group = em()->find(TeamGroup::class, $pool_id);
if (!$team_group || $team_group->event->id !== $event->id) {
    Toast::error("Pool d'équipes introuvable");
    redirect("/evenements/$event_id?tab=pools");
}

$v = new Validator();

if ($v->valid()) {
    $team_group->published = !$team_group->published;
    em()->persist($team_group);
    em()->flush();
    $team_group->published ? Toast::success("Pool publié") : Toast::success("Pool retiré");
    redirect("/evenements/$event_id/pool/$pool_id");
}

page(($team_group->published ? "Retirer" : "Publier") . " - " . ($team_group->name ?: "Pool #$pool_id"));
?>

<?= actions()->back("/evenements/$event_id/pool/$pool_id") ?>

<div class="container">
    <article>
        <form method="post" class="center">
            <?= $v->render_validation() ?>
            <p>
                Sûr de vouloir <?= $team_group->published ? "retirer" : "publier" ?> le pool
                <strong><?= htmlspecialchars($team_group->name ?: "Pool #$pool_id") ?></strong> ?
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
                        href="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>">Annuler</a>
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