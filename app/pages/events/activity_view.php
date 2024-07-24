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

<?= actions()->back($link ?: "/evenements")
    ->if(
        $can_edit,
        // I love functional programming :D
        fn($a) => $a->dropdown(fn($b) => $b
            ->link("$link/activite/$activity->id/modifier", "Éditer", "fas fa-pen")
            ->link("$link/activite/$activity->id/supprimer", "Supprimer", "fas fa-trash", ["class" => "destructive"]))
    ) ?>

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