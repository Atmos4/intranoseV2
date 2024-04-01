<?php
include_once __DIR__ . "/RenderEvents.php";

restrict_access();
$can_edit = check_auth(Access::$ADD_EVENTS);

formatter("d MMM");
$user = User::getCurrent();
$future_events = Event::listAllFutureOpen($user->id);
if ($can_edit)
    $draft_events = Event::listDrafts();

// Get the current date
$current_date_month = date('m');
$current_date_day = date('d');

// Query the database to get a list of users whose birthday matches the current date
$birthday_users = em()->createQueryBuilder()
    ->select("u")
    ->from(User::class, "u")
    ->where("MONTH(u.birthdate) = :month AND DAY(u.birthdate) = :day")
    ->setParameter("month", $current_date_month)
    ->setParameter("day", $current_date_day)
    ->getQuery()->getResult();

page("Événements")->css("event_list.css")->heading(false);

$vowels = array("a", "e", "i", "o", "u"); ?>

<?php if ($birthday_users): ?>
    <div class="birthday-wrapper">
        <?php foreach ($birthday_users as $birthday_user): ?>
            <div class="birthday">
                🎂 C'est l'anniversaire
                <?= ((in_array(strtolower(substr($birthday_user->first_name, 0, 1)), $vowels)) ? "d'" : "de ")
                    . "$birthday_user->first_name $birthday_user->last_name 🎉" ?>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>

<h2 class="center">Événements</h2>


<?php if ($can_edit): ?>
    <nav id="page-actions">
        <a href="/evenements/nouveau"><i class="fas fa-plus"></i> Ajouter un
            événement</a>
    </nav>
<?php endif ?>

<?php if (!count($future_events) && !($can_edit && count($draft_events))): ?>
    <p class="center">Pas d'événement pour le moment 😴</p>
<?php endif ?>

<?php // Draft events 
if ($can_edit && count($draft_events)): ?>

    <h6>Événements en attente</h6>
    <?php
    foreach ($draft_events as $draft_event) {
        render_events_article($draft_event);
    } ?>
    <h6>Événements publiés</h6>
<?php endif ?>

<?php if (count($future_events)): ?>
    <?php foreach ($future_events as $event): ?>
        <?= render_events_article($event); ?>
    <?php endforeach ?>
<?php endif ?>

<div id="loadEvents">
    <button class="outline secondary" hx-get="/evenements/passes" hx-swap="outerHTML" hx-target="this">Charger
        les
        événements
        passés</button>
</div>