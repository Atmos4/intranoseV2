<?php
restrict_access();

$activity = em()->find(Activity::class, get_route_param("activity_id"));

if ($activity->type == ActivityType::RACE) {
    $icon = "fa fa-stopwatch";
} elseif ($activity->type == ActivityType::TRAINING) {
    $icon = "fa fa-dumbbell";
} else {
    $icon = "fa fa-bowl-food";
}

page($activity->name)->css("event_view.css");
?>

<nav id="page-actions">
    <?php $link = $activity->event ? "/evenements/{$activity->event->id}" : "/evenements" ?>
    <a href="<?= $link ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

</nav>

<article>
    <header class="center">
        <?php if ($activity->type == ActivityType::RACE) {
            $type = "Course";
        } elseif ($activity->type == ActivityType::TRAINING) {
            $type = "Entrainement";
        } else {
            $type = "Autre";
        } ?>

        <p>
            Type : <kbd>
                <?= $type ?>
            </kbd>
        </p>
        <div class="row">
            <div>
                <?php include app_path() . "/components/start_icon.php" ?>
                <span>
                    <?= "Date : " . format_date($activity->date) ?>
                </span>
            </div>
        </div>
    </header>
    <?php if ($activity->event->open): ?>
        <div class="grid">
            <?php $activity_entry = $activity->entries[0] ?? null; ?>
            <ul class="fa-ul">
                <?php if ($activity_entry?->present): ?>

                    <li><ins><span class="fa-li"><i class="fa fa-check"></i></span>Inscrit</ins></li>

                <?php else: ?>
                    <del>
                        <li><span class="fa-li"><i class="fa fa-xmark"></i></span>
                            <?= $activity_entry ? "Je ne participe pas" : "Pas encore inscrit" ?>
                        </li>
                    </del>
                <?php endif; ?>
            </ul>
            <?php if ($activity_entry?->category): ?>
                <ul class="fa-ul">
                    <li><span class="fa-li"><abbr title="Catégorie"><i class="fa fa-person-running"></i></abbr></span>
                        <?= $activity_entry->category?->name ?>
                    </li>
                </ul>
            <?php endif ?>
        </div>
        <?php if ($activity_entry?->comment): ?>
            <div>
                <cite>Remarque : </cite>
                <?= $activity_entry->comment ?>
            </div>
        <?php endif; ?>
        <?php if ($activity->description): ?>
            <h5>Description</h5>
            <p>
                <?= $activity->description ?>
            </p>
        <?php endif; ?>
    <?php endif ?>