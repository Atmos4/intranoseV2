<?php

restrict_access();
restrict_feature(Feature::Calendar);

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$firstDayOfMonth = new DateTime("$year-$month-01");
$startDate = (clone $firstDayOfMonth)->modify("Monday this week");
$endDate = (clone $firstDayOfMonth)->modify('last day of this month')->modify("Sunday this week");
$events = EventService::getEventsForPeriod($startDate, $endDate);


$eventsByDate = [];
foreach ($events as $event) {
    $sd = clone $event->start_date;
    $ed = clone $event->end_date;
    if ($sd >= $ed) {
        $dateStr = $sd->format('Y-m-d');
        $eventsByDate[$dateStr][] = $event;
        continue;
    }
    while ($sd <= $ed) {
        $dateStr = $sd->format('Y-m-d');
        $eventsByDate[$dateStr][] = $event;
        $sd->modify("+1 day");
    }
}

// Previous and Next Month Links
$prevMonth = (clone $firstDayOfMonth)->modify('-1 month');
$nextMonth = (clone $firstDayOfMonth)->modify('+1 month');

page("Calendrier")->css("event_list.css")->css("event_calendar.css")->noPadding()->heading(false) ?>
<div class="calendar-wrapper">
    <div class="calendar-header">
        <h3 class="main-heading">
            <a
                href="/evenements/calendrier?year=<?= $prevMonth->format('Y') ?>&month=<?= $prevMonth->format('m') ?>">&laquo;</a>
            <span><?= $firstDayOfMonth->format('F Y') ?></span>
            <a
                href="/evenements/calendrier?year=<?= $nextMonth->format('Y') ?>&month=<?= $nextMonth->format('m') ?>">&raquo;</a>
        </h3>
        <a style="margin-left:2rem" href="/evenements">
            <sl-tooltip content="Liste"><i class="fa fa-list"></i></sl-tooltip>
        </a>
    </div>

    <div class="grid-table" hx-target="#event-list" hx-swap="innerHTML">
        <div class="grid-h">L</div>
        <div class="grid-h">M</div>
        <div class="grid-h">M</div>
        <div class="grid-h">J</div>
        <div class="grid-h">V</div>
        <div class="grid-h">S</div>
        <div class="grid-h">D</div>
        <?php
        $day = clone $startDate;
        while ($day <= $endDate) {
            $dateStr = $day->format("Y-m-d");
            $dayNr = $day->format("d");
            $className = $day->format("m") == $month ? "" : "class=\"faint\"";
            if ($dateStr == date("Y-m-d")) {
                $className = "class=\"today\"";
            }
            echo "<div class=\"inner-div\" hx-get=\"/cal/view?date=$dateStr\">
                <strong $className>$dayNr</strong>
                <div class=\"event-wrapper\">";
            if (isset($eventsByDate[$dateStr])) {
                foreach ($eventsByDate[$dateStr] as $event) {
                    echo "<span class='event'></span>";
                }
            }
            echo "</div></div>";
            $day->modify("+1 day");
        }
        ?>
    </div>
    <div id="event-list" style="padding:1rem">
        <p style="text-align:center">Sélectionnez un jour</p>
    </div>
</div>
