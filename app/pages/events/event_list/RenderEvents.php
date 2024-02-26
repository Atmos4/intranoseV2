<?php

function render_event(EventDto $event)
{
    $diff = date_create('now')->diff($event->deadline->add(new DateInterval("PT23H59M59S")));
    $td_class = $tooltip_content = "";
    if ($diff->invert) {
        $td_class = "passed";
        $tooltip_content = "data-tooltip='Deadline dépassée'";
    } elseif ($diff->days < 7) {
        $td_class = "warning";
        $tooltip_content = "data-tooltip=\""
            . ($diff->days == 0 ?
                $diff->format("Plus que %h heures!") :
                $diff->format("Dans %d jour" . ($diff->days == 1 ? "" : "s")))
            . "\"";
    } ?>
    <tr class="event-row clickable" tabindex="0" hx-trigger="click,keyup[key=='Enter']"
        hx-get="/evenements/<?= $event->id ?>" hx-target="body" hx-push-url="true">
        <td class="event-entry">

            <?php if ($event->open):
                if ($event->registered == true): ?>
                    <ins><i class="fas fa-check"></i></ins>
                <?php elseif ($event->registered === false): ?>
                    <del><i class="fas fa-xmark"></i></del>
                <?php else: ?>
                    <i class="fas fa-question"></i>
                <?php endif; else: ?>
                <i class="fas fa-file"></i>
            <?php endif ?>

        </td>
        <td class="event-name"><b>
                <?= $event->name ?>
            </b></td>
        <td class="event-date">
            <span>
                <?= format_date($event->start) ?>
            </span><i class="fas fa-arrow-right"></i><span>
                <?= format_date($event->end) ?>
            </span>
        </td>
        <td class="event-limit <?= $td_class ?>">
            <i class="fas fa-clock"></i><span <?= $tooltip_content ?> data-placement='left'>
                <?= format_date($event->deadline) ?>
            </span>
        </td>
    </tr>

<?php }

function render_events_article(EventDto $event)
{
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

    <article class="event-article" onclick="location.href='/evenements/<?= $event->id ?>';" style="cursor: pointer;">
        <div class="grid">
            <div class="icon">
                <?php if ($event->open):
                    if ($event->registered == true): ?>
                        <ins><i class="fas fa-check fa-xl"></i></ins>
                    <?php elseif ($event->registered === false): ?>
                        <del><i class="fas fa-xmark fa-xl"></i></del>
                    <?php else: ?>
                        <i class="fas fa-question fa-xl"></i>
                    <?php endif; else: ?>
                    <i class="fas fa-file"></i>
                <?php endif ?>
            </div>

            <div class="title">
                <b>
                    <?= $event->name ?>
                </b>
                <hr>
            </div>
            <div class="dates">
                <span>
                    <?= format_date($event->start) ?>
                </span><i class="fas fa-arrow-right"></i><span>
                    <?= format_date($event->end) ?>
                </span>
            </div>
            <div class="event-limit <?= $limit_class ?>">
                <i class="fas fa-clock"></i><span <?= $tooltip_content ?> data-placement='left'>
                    <?= format_date($event->deadline) ?>
                </span>
            </div>
            <div class="info-button"><a href="/evenements/<?= $event->id ?>" role="button" class="secondary"><i
                        class="fas fa-info fa-lg"></i></a></div>
        </div>
    </article>
    <?php
}