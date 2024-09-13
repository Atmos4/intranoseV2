<?php
include_once __DIR__ . "/RenderEvents.php";

restrict_access();
$can_edit = check_auth(Access::$ADD_EVENTS);

formatter("d MMM");
$user = User::getCurrent();
$future_events = EventService::listAllFutureOpenEvents($user->id);
if ($can_edit)
    $draft_events = EventService::listDrafts();

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

$vowels = array("a", "e", "i", "o", "u");
?>

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

<?= actions($can_edit)->link("#blank", "Ajouter un événement", "fas fa-plus", ["onclick" => "document.getElementById('eventDialog').setAttribute('open','')"]) ?>

<?php if (!count($future_events) && !($can_edit && count($draft_events))): ?>
    <p class="center">Pas d'événement pour le moment 😴</p>
<?php endif ?>

<?php // Draft events 
if ($can_edit && count($draft_events)): ?>

    <h6>Événements en attente</h6>
    <?php
    foreach ($draft_events as $draft_event) {
        render_events($draft_event);
    } ?>
    <h6>Événements publiés</h6>
<?php endif ?>

<?php foreach ($future_events as $event): ?>
    <?= render_events($event); ?>
<?php endforeach ?>

<div id="loadEvents">
    <button class="outline secondary" hx-get="/evenements/passes" hx-swap="outerHTML" hx-target="this">Charger
        les
        événements
        passés</button>
</div>


<dialog id="eventDialog" onclick="event.target=== this && htmx.trigger(this, 'close-modal')" hx-on:close-modal="this.classList.add('closing');
    this.addEventListener('animationend', () => {
        this.close();
    }, {once:true})" hx-on:close="this.close();this.classList.remove('closing');">
    <article>
        <header>
            <button class=" close secondary" role="link" onclick="htmx.trigger(this, 'close-modal')">
            </button>
            <b>
                Type d'événement
            </b>
        </header>

        <p>
        <ul>
            <li><b>Simple</b> si il ne contient qu'une seule activité sans déplacement (entraînement
                au niveau local, événement ponctuel,...)</li>
            <li><b>Complexe</b> si il s'agit d'un événement plus long avec déplacement et/ou plusieurs activités</li>
        </ul>
        </p>

        <footer class="center">
            <a role="button" href="/evenements/nouveau?type=simple">Simple</a>
            <a role="button" href="/evenements/nouveau?type=complex">Complexe</a>
        </footer>
    </article>
</dialog>