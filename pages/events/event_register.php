<?php
restrict_access();

$id = $_SESSION['user_id'];

require_once "database/events.api.php";
require_once "utils/form_validation.php";

$user = em()->find(User::class, $id);

$event = Event::getWithGraphData(get_route_param('event_id'), $_SESSION['user_id']);

if (!$event->open || $event->deadline < date_create("today")) {
    force_404("this event is closed for entry");
}

$event_entry = $event->entries[0] ?? null;
$form_values = [];

if ($event_entry) {
    $form_values = [
        "event_present" => $event_entry->present,
        "event_transport" => $event_entry->transport,
        "event_accomodation" => $event_entry->accomodation,
        "event_comment" => $event_entry->comment,
        "event_comment_noentry" => $event_entry->comment,
    ];
}

foreach ($event->races as $index => $race) {
    $race_entry = $race->entries[0] ?? null;
    if ($race_entry) {
        $form_values["race_{$index}_entry"] = $race_entry->present;
        $form_values["race_{$index}_ranked_up"] = $race_entry->upgraded;
        $form_values["race_{$index}_comment"] = $race_entry->comment;
    }
}

$v = validate($form_values ?? []);
$event_present = $v->switch("event_present")->set_labels("Je participe", "Pas inscrit");
$transport = $v->switch("event_transport")->label("Transport");
$accomodation = $v->switch("event_accomodation")->label("Hébergement");
$event_comment = $v->text("event_comment")->area()->label("Remarques");
$event_comment_noentry = $v->text("event_comment_noentry")->area()->label("Remarque");
$race_rows = [];
foreach ($event->races as $index => $race) {
    $race_rows[$index]["entry"] = $v->switch("race_{$index}_entry")->set_labels("Je cours", "Je ne cours pas");
    $race_rows[$index]["ranked_up"] = $v->switch("race_{$index}_ranked_up")->label("Surclassé");
    $race_rows[$index]["comment"] = $v->text("race_{$index}_comment")->area()->label("Remarque");
}

if ($v->valid()) {
    $event_entry ??= new EventEntry();
    $event_entry->set(
        $user,
        $event,
        $event_present->value,
        $event_present->value && $transport->value,
        $event_present->value && $accomodation->value,
        date_create(),
        $event_present->value ? $event_comment->value : $event_comment_noentry->value,
    );
    em()->persist($event_entry);

    foreach ($event->races as $index => $race) {
        $race_entry = $race->entries[0] ?? new RaceEntry();
        $race_form = $race_rows[$index];
        $race_entry->set(
            $user,
            $race,
            $event_present->value && $race_form["entry"]->value,
            $event_present->value && $race_form["ranked_up"]->value,
            $user->licence,
            $user->sportident,
            $race_form["comment"]->value,
        );
        em()->persist($race_entry);
    }
    em()->flush();
    redirect("/evenements/$event->id");
}

page("Inscription - " . $event->name, "event_view.css");
?>
<form id="mainForm" method="post">
    <div id="page-actions">
        <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
        <div><button type="submit" role="button">Enregistrer</button></div>
    </div>
    <article>
        <header class="center">
            <?= $v->render_errors() ?>
            <div class="row">
                <div class="col-sm-6">
                    <?php include "components/start_icon.php" ?>

                    <span>
                        <?= "Départ - " . format_date($event->start_date) ?>
                    </span>
                </div>
                <div class="col-sm-6">
                    <?php include "components/finish_icon.php" ?>
                    <span>
                        <?= "Retour - " . format_date($event->end_date) ?>
                    </span>
                </div>
                <div>
                    <i class="fas fa-clock"></i>
                    <span>
                        <?= "Date limite - " . format_date($event->deadline) ?>
                    </span>
                </div>
            </div>

            <fieldset>
                <b>
                    <?= $event_present->render("onchange=\"toggleDisplay(this,'eventForm')\"") ?>
                </b>
            </fieldset>
        </header>

        <div id="eventForm" <?= $event_present->value ?: "class='hidden'" ?>>

            <fieldset class="row">
                <div class="col-sm-6">
                    <?= $transport->render() ?>
                </div>
                <div class="col-sm-6">
                    <?= $accomodation->render() ?>
                </div>
            </fieldset>
            <fieldset>
                <?= $event_comment->render() ?>
            </fieldset>

            <?php if (count($event->races)): ?>
                <h4>Courses : </h4>
                <table role="grid">
                    <?php foreach ($event->races as $index => $race):
                        $race_form = $race_rows[$index]; ?>
                        <tr class="display">
                            <td class="race-name"><b>
                                    <?= $race->name ?>
                                </b></td>
                            <td class="race-date">
                                <?= format_date($race->date) ?>
                            </td>
                            <td class="race-place">
                                <?= $race->place ?>
                            </td>
                        </tr>
                        <tr class="edit">
                            <td colspan="3">
                                <fieldset class="row">
                                    <?= $race_form["entry"]->render("onchange=\"toggleDisplay(this,'raceForm$index')\"") ?>
                                    <div id="raceForm<?= $index ?>" <?= $race_form['entry']->value ?: " class=hidden" ?>>
                                        <?= $race_form["ranked_up"]->render() ?>
                                    </div>
                                    <?= $race_form["comment"]->render() ?>
                                </fieldset>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>
        </div>
        <div id="conditionalText">
            <p>Inscris-toi pour une vraie partie de plaisir !</p>
            <fieldset>
                <?= $event_comment_noentry->render() ?>
            </fieldset>
        </div>
    </article>
</form>

<script>

    function toggleDisplay(toggle, target) {
        const targetElement = document.getElementById(target);
        if (toggle.checked) {
            targetElement.classList.remove("hidden");
        } else {
            targetElement.classList.add("hidden");
        }
    }
</script>