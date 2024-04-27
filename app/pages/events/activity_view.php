<?php
restrict_access();

include __DIR__ . "/eventUtils.php";

$event_id = get_route_param("event_id", false);
$activity = em()->find(Activity::class, get_route_param("activity_id"));

$activity_entry = $activity->entries[0] ?? null;
$can_edit = check_auth(Access::$ADD_EVENTS);
$link = $event_id ? "/evenements/$event_id" : "";

page($activity->name)->css("event_view.css");
?>

<nav id="page-actions">
    <a href="<?= $link ?: "/evenements" ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <?php if ($can_edit): ?>
        <li>
            <details class="dropdown">
                <summary>Actions</summary>
                <ul dir="rtl">
                    <li><a href="<?= $link ?>/activite/<?= $activity->id ?>/modifier" class="secondary">
                            <i class="fas fa-pen"></i> Éditer
                        </a></li>
                    <li>
                        <a href="<?= $link ?>/activite/<?= $activity->id ?>/supprimer" class="destructive">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </li>
                </ul>
            </details>
        </li>

    <?php endif ?>
</nav>

<?= ActivityEntry($activity_entry) ?>

<article>
    <header class="grid">
        <div>
            <?= IconText($activity->type->toIcon(), $activity->type->toName()) ?>
        </div>
        <div>
            <?= IconText("fa-calendar", format_date($activity->date)) ?>
        </div>
        <div>
            <span>
                <i class="fa fa-location-dot fa-fw"></i>
                <?php if ($activity->location_url): ?>
                    <a href=<?= $activity->location_url ?> target=”_blank”><?= $activity->location_label ?></a>
                <?php else: ?>
                    <?= $activity->location_label ?>
                <?php endif ?>
            </span>
        </div>
    </header>
    <?php if ($activity->description): ?>
        <section>
            <h3>Description</h3>
            <?= (new Parsedown)->text($activity->description) ?>
        </section>
    <?php endif; ?>
</article>