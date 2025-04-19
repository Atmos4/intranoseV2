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

page("Calendrier")->css("event_list.css")->noPadding()->heading(false) ?>
<div class="calendar-wrapper">
    <div style="display:flex;padding-bottom:0.5rem;align-items:center;padding:0.5rem 1rem">
        <h2 class="main-heading" style="margin:0">
            <a
                href="/evenements/calendrier?year=<?= $prevMonth->format('Y') ?>&month=<?= $prevMonth->format('m') ?>">&laquo;</a>
            <?= $firstDayOfMonth->format('F Y') ?>
            <a
                href="/evenements/calendrier?year=<?= $nextMonth->format('Y') ?>&month=<?= $nextMonth->format('m') ?>">&raquo;</a>
        </h2>
        <a style="margin-left:2rem" href="/evenements" role="button">
            <sl-tooltip content="Liste"><i class="fa fa-list"></i></sl-tooltip>
        </a>
    </div>

    <style>
        div.calendar-wrapper {
            padding: 1rem 0;
            margin: 0 auto;
        }

        .grid-table {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .inner-div,
        .grid-h {
            padding: 5px 0;
            height: 50px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }

        .inner-div {
            border-top: 1px solid var(--pico-muted-border-color);
            transition: background-color ease-in 0.1s;
        }

        .inner-div:hover {
            background-color: #444;
            cursor: pointer;
        }

        .grid-h {
            font-size: 10px;
            height: 20px;
        }

        .inner-div>strong {
            font-size: 12px;
        }

        .inner-div>strong.faint {
            opacity: 0.6;
        }

        .inner-div>strong.today {
            color: var(--pico-primary);
        }

        .event-wrapper {
            display: flex;
            justify-content: center;
        }

        .event {
            width: 10px;
            height: 10px;
            display: block;
            background-color: var(--pico-primary);
            margin: 2px;
            border-radius: 99ch;
        }
    </style>
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
    <div id="event-list" style="padding:1rem"></div>
</div>