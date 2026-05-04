<?php
function render_events(EventDto $event)
{
    $diff = date_create('now')->diff($event->deadline);
    $limit_class = $tooltip_content = "";
    if ($diff->invert) {
        $limit_class = "passed";
        $tooltip_content = "Deadline dépassée";
    } elseif ($diff->days < 7) {
        $limit_class = "warning";
        $tooltip_content = ($diff->days == 0 ?
            $diff->format("Plus que %h heures!") :
            $diff->format("Dans %d jour" . ($diff->days == 1 ? "" : "s")));
    }
    $groups = GroupService::getEventGroups($event->id); ?>

    <article class="event-article" hx-trigger="click,keyup[key=='Enter'||key==' ']" onkeydown="console.log(event.key)"
        hx-get="/evenements/<?= $event->id ?>" hx-target="body" hx-push-url="true" tabindex=0>
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
            <div class="grid-tag">
                <?= GroupService::renderDots($groups) ?>
            </div>
            <div class="dates">
                <?php if (!($event->end == $event->start)): ?>
                    <div class="start-end">
                        <div class="start-bloc">
                        <?php endif ?>
                        <div class="date-hour">
                            <div class="date">
                                <span>
                                    <?= format_date($event->start, 'dd MMM') ?>
                                </span>
                            </div>
                            <div class="hour">
                                <span>
                                    <?= format_date($event->deadline, 'HH:mm') ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!($event->end == $event->start)): ?>
                        </div>
                        <div class="arrow-bloc">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="end-bloc">
                            <div class="date-hour">
                                <div class="date">
                                    <span>
                                        <?= format_date($event->start, 'dd MMM') ?>
                                    </span>
                                </div>
                                <div class="hour">
                                    <span>
                                        <?= format_date($event->deadline, 'HH:mm') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            </div>
            <div class="event-limit <?= $limit_class ?>">
                <?php if ($tooltip_content): ?>
                    <sl-tooltip content="<?= $tooltip_content ?>">
                    <?php endif ?>
                    <div class="date-hour with-clock">
                        <div class="clock"><i title="Deadline" class="fas fa-clock"></i></div>
                        <div class="date">
                            <span>
                                <?= format_date($event->deadline, 'dd MMM') ?>
                            </span>
                        </div>
                        <div class="hour">
                            <span>
                                <?= format_date($event->deadline, 'HH:mm') ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($tooltip_content): ?>
                    </sl-tooltip>
                <?php endif ?>
            </div>
        </div>
    </article>
    <?php
}