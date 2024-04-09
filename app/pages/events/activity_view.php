<?php
restrict_access();

include __DIR__ . "/eventUtils.php";

$activity = em()->find(Activity::class, get_route_param("activity_id"));

if ($activity->type == ActivityType::RACE) {
    $icon = "fa fa-stopwatch";
} elseif ($activity->type == ActivityType::TRAINING) {
    $icon = "fa fa-dumbbell";
} else {
    $icon = "fa fa-bowl-food";
}
$activity_entry = $activity->entries[0] ?? null;

page($activity->name)->css("event_view.css");
?>

<nav id="page-actions">
    <?php $link = $activity->event ? "/evenements/{$activity->event->id}" : "/evenements" ?>
    <a href="<?= $link ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

</nav>

<?= ActivityEntry($activity_entry) ?>

<article>
    <div class="grid">
        <div>
            <?= IconText($activity->type->toIcon(), $activity->type->toName()) ?>
        </div>
        <div>
            <?= IconText("fa-calendar", format_date($activity->date)) ?>
        </div>
    </div>
    <?php if ($activity->event->open): ?>
        <?php if ($activity->description): ?>
            <h3>Description</h3>
            <p>
                <?= $activity->description ?>
            </p>
        <?php endif; ?>
    <?php endif ?>