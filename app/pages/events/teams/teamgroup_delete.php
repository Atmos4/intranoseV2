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
    em()->remove($team_group);
    em()->flush();

    Toast::success("Pool d'équipes supprimé");
    redirect("/evenements/$event_id?tab=pools");
}

page("Confirmation de suppression");
?>

<?= actions()->back("/evenements/$event_id?tab=pools") ?>

<form method="POST">
    <?= $v->render_validation() ?>
    <div class="row center">
        <p>Sûr de vouloir supprimer le pool <strong><?= $team_group->name ?: "Pool #$pool_id" ?></strong> ?</p>
        <p class="row">
            <span>
                <i class="fa fa-chevron-right"></i>
                Équipes: <?= count($team_group->teams) ?>
            </span>
            <span>
                <i class="fa fa-chevron-right"></i>
                Membres: <?php
                $total = 0;
                foreach ($team_group->teams as $t)
                    $total += count($t->members);
                echo $total;
                ?>
            </span>
        </p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/evenements/<?= $event_id ?>?tab=pools">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit" name="delete" value="true" class="destructive">Supprimer</button>
        </div>
    </div>
</form>