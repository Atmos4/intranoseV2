<?php
restrict_access();
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);
$can_edit = check_auth(Access::$ADD_EVENTS);

$team_groups = em()->getRepository(TeamGroup::class)->findBy(['event' => $event], ['id' => 'ASC']);
$is_simple = $event->type == EventType::Simple;
?>

<div class="container">
    <table>
        <?php foreach ($team_groups as $team_group): ?>
            <?php if ($team_group->published || !$team_group->published && $can_edit): ?>
                <article class="pool-article" hx-trigger="click,keyup[key=='Enter'||key==' ']"
                    hx-get="/evenements/<?= $event_id ?>/pool/<?= $team_group->id ?>" hx-target="body" hx-push-url="true"
                    tabindex=0>
                    <div class="pool-grid">
                        <div class="pool-icon">
                            <i class="fa fa-people-group"></i>
                        </div>
                        <div class="pool-title">
                            <b><?= $team_group->name ?: "Pool #" . $team_group->id ?></b>
                            <?php if ($can_edit): ?>
                                <?php if ($team_group->published): ?>
                                    <span class="badge"><i class="fa fa-eye"></i></span>
                                <?php else: ?>
                                    <span class="badge secondary"><i class="fa fa-eye-slash"></i></span>
                                <?php endif ?>
                            <?php endif ?>
                        </div>
                        <div class="pool-stats">
                            <div class="pool-stat">
                                <i class="fa fa-users"></i>
                                <span><?= count($team_group->teams) ?>
                                    équipe<?= count($team_group->teams) > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                        <?php if ($can_edit): ?>
                            <div class="pool-actions" onclick="event.stopPropagation();">
                                <a role="button" class="secondary outline"
                                    href="/evenements/<?= $event_id ?>/pool/<?= $team_group->id ?>/modifier">
                                    <i class="fa fa-pen"></i>
                                </a>
                                <a role="button" class="secondary outline"
                                    href="/evenements/<?= $event_id ?>/pool/<?= $team_group->id ?>/supprimer">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        <?php endif ?>
                    </div>
                </article>
            <?php endif ?>
        <?php endforeach;
        if (empty($team_groups)): ?>
            <p class="center">Pas encore de pools d'équipes 🏃</p>
        <?php endif ?>
    </table>
    <?php if ($can_edit): ?>
        <a role="button" class="secondary" href="/evenements/<?= $event_id ?>/pool/nouveau">
            <i class="fa fa-plus"></i> Nouveau Pool d'Équipes
        </a>
    <?php endif ?>
</div>