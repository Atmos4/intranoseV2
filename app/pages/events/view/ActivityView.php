<?php
restrict_access();

$event_id = get_route_param("event_id", false);
$event = em()->find(Event::class, $event_id);
if ($event->type == EventType::Simple) {
    $activity = $event->activities[0];
} else {
    $activity = em()->find(Activity::class, get_route_param("activity_id"));
}

?>

<article>
    <div class="horizontal">
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
    </div>
    <?php if ($activity->description): ?>
        <hr>
        <h3>Description</h3>
        <?= (new Parsedown)->text($activity->description) ?>
    <?php endif; ?>
</article>