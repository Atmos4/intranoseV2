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

        <div class="row">
            <?php
            $activity_entry = $activity->entries[0] ?? null;
            if ($activity->event->open): ?>
                <?php if ($activity_entry): ?>
                    <?php if ($activity_entry->present): ?>
                        <ins><i class="fas fa-check fa-lg"></i></ins>
                    <?php else: ?>
                        <del><i class="fas fa-xmarkf fa-lg"></i></del>
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-question fa-lg"></i>
                <?php endif; ?>
            <?php else: ?>
                <del>Pas encore publié</del>
            <?php endif ?>
        </div>
    </header>
    <?php if ($activity->description): ?>
        <p>
            <?= $activity->description ?>
        </p>
    <?php endif; ?>
    <?php if ($activity->event->open): ?>
        <blockquote>
            <h6>Inscription</h6>
            <div class="grid">
                <ul class="fa-ul">
                    <?php if ($activity_entry?->present): ?>
                        <li><span class="fa-li"><i class="fa fa-check"></i></span><ins>Je participe</ins></li>
                    <?php else: ?>
                        <li><span class="fa-li"><i class="fa fa-xmark"></i></span><del>
                                <?= $activity_entry ? "Je ne participe pas" : "Pas inscrit" ?>
                            </del></li>
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
        </blockquote>
    <?php endif ?>