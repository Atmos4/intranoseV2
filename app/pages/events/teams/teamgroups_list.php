<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);

$team_groups = em()->getRepository(TeamGroup::class)->findBy(['event' => $event], ['id' => 'ASC']);
?>

<div class="container">
    <table>
        <?php foreach ($team_groups as $team_group): ?>
            <?php
            $total_members = 0;
            foreach ($team_group->teams as $team) {
                $total_members += count($team->members);
            }
            ?>
            <article class="pool-article" hx-trigger="click,keyup[key=='Enter'||key==' ']"
                hx-get="/evenements/<?= $event_id ?>/pool/<?= $team_group->id ?>" hx-target="body" hx-push-url="true"
                tabindex=0>
                <div class="pool-grid">
                    <div class="pool-icon">
                        <i class="fa fa-users-gear"></i>
                    </div>
                    <div class="pool-title">
                        <b><?= $team_group->name ?: "Pool #" . $team_group->id ?></b>
                    </div>
                    <div class="pool-stats">
                        <div class="pool-stat">
                            <i class="fa fa-users"></i>
                            <span><?= count($team_group->teams) ?>
                                équipe<?= count($team_group->teams) > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="pool-stat">
                            <i class="fa fa-user"></i>
                            <span><?= $total_members ?> membre<?= $total_members > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                    <div class="pool-actions" onclick="event.stopPropagation();">
                        <a role="button" class="secondary outline"
                            href="/evenements/<?= $event_id ?>/pool/<?= $team_group->id ?>/supprimer">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach;
        if (empty($team_groups)): ?>
            <p class="center">Pas encore de pools d'équipes 🏃</p>
        <?php endif ?>
    </table>
    <a role="button" href="/evenements/<?= $event_id ?>/pool/nouveau">
        <i class="fa fa-plus"></i> Nouveau Pool d'Équipes
    </a>
</div>