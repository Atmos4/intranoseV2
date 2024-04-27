<?php
function render_events_article(EventDto $event)
{
    $url = match ($event->type) {
        EventType::Event => "/evenements/$event->id",
        EventType::Activity => "/activite/$event->id",
    };

    $diff = date_create('now')->diff($event->deadline->add(new DateInterval("PT23H59M59S")));
    $limit_class = $tooltip_content = "";
    if ($diff->invert) {
        $limit_class = "passed";
        $tooltip_content = "data-tooltip='Deadline dépassée'";
    } elseif ($diff->days < 7) {
        $limit_class = "warning";
        $tooltip_content = "data-tooltip=\""
            . ($diff->days == 0 ?
                $diff->format("Plus que %h heures!") :
                $diff->format("Dans %d jour" . ($diff->days == 1 ? "" : "s")))
            . "\"";
    } ?>

    <article class="event-article" hx-trigger="click,keyup[key=='Enter'||key==' ']" onkeydown="console.log(event.key)"
        hx-get="<?= $url ?>" hx-target="body" hx-push-url="true" tabindex=0>
        <div class="grid">
            <div class="icon">
                <?php if ($event->open):
                    if ($event->registered == true): ?>
                        <ins><i class="fas fa-check fa-xl" title="Inscrit !"></i></ins>
                    <?php elseif ($event->registered === false): ?>
                        <del><i class="fas fa-xmark fa-xl" title="Pas inscrit"></i></del>
                    <?php else: ?>
                        <i class="fas fa-question fa-xl" title="Pas encore inscrit"></i>
                    <?php endif; else: ?>
                    <i class="fas fa-file" title="Brouillon"></i>
                <?php endif ?>
            </div>

            <div class="title">
                <b>
                    <?= $event->name ?>
                </b>
            </div>
            <div class="dates">
                <span>
                    <?= format_date($event->start) ?>
                </span>
                <?php if ($event->end): ?>
                    <i class="fas fa-arrow-right"></i>
                    <span>
                        <?= format_date($event->end) ?>
                    </span>
                <?php endif ?>
            </div>
            <div class="event-limit <?= $limit_class ?>">
                <i title="Deadline" class="fas fa-clock"></i><span <?= $tooltip_content ?> data-placement='left'>
                    <?= format_date($event->deadline) ?>
                </span>
            </div>
        </div>
    </article>
    <?php
}