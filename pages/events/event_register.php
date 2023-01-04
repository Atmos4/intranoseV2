<?php
restrict_access();

$id = $_SESSION['user_id'];

require_once "database/events.api.php";
require_once "database/users.api.php";
require_once "utils/form_validation.php";

$user = get_user($id);

$event = Event::single_from_db(get_route_param('event_id'), $_SESSION['user_id']);
$competitions = get_competitions_by_event_id($event->id, $_SESSION['user_id']);
$event_form_values = EventEntry::to_form($event->entry);

foreach ($competitions as $competition) {
    $event_form_values["competition_{$competition['cid']}_entry"] = $competition['present'];
    $event_form_values["competition_{$competition['cid']}_ranked_up"] = $competition['surclasse'];
    $event_form_values["competition_{$competition['cid']}_comment"] = $competition['rmq'];
}


$v = validate($event_form_values ?? []);
$event_entry = $v->switch("event_entry")->set_labels("Je participe", "Pas inscrit");
$transport = $v->switch("event_transport")->label("Transport");
$accomodation = $v->switch("event_accomodation")->label("Hébergement");
$event_comment = $v->text("event_comment")->area()->label("Remarques");
$competition_rows = [];
foreach ($competitions as $competition) {
    $competition_rows[$competition['cid']] = $competition;
    $competition_rows[$competition['cid']]["entry"] = $v->switch("competition_{$competition['cid']}_entry")->set_labels("Je cours", "Je ne cours pas");
    $competition_rows[$competition['cid']]["ranked_up"] = $v->switch("competition_{$competition['cid']}_ranked_up")->label("Surclassé");
    $competition_rows[$competition['cid']]["comment"] = $v->text("competition_{$competition['cid']}_comment")->area()->label("Remarques");
}

if ($v->valid()) {
    EventEntry::create(
        $event->id,
        $id,
        $event_entry->value,
        $event_entry->value && $transport->value,
        $event_entry->value && $accomodation->value,
        date("Y-m-d H-m-s"),
        $event_comment->value,
    )->save_in_db();

    foreach ($competition_rows as $race_id => $competition) {
        RaceEntry::create(
            $race_id,
            $id,
            $event_entry->value && $competition["entry"]->value,
            $event_entry->value && $competition["ranked_up"]->value,
            $user["num_lic"],
            $user["sportident"],
            $competition["comment"]->value,
        )->save_in_db();
    }
}

page("Inscription - " . $event->name, "event_view.css");
?>
<form id="mainForm" method="post">
    <div id="page-actions">
        <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
        <a href="#" onclick="document.getElementById('mainForm').submit()">Enregistrer</a>
    </div>
    <article>
        <header class="center">
            <?= $v->render_errors() ?>
            <div class="row">
                <div class="col-sm-6">
                    <?php include "components/start_icon.php" ?>

                    <span>
                        <?= "Départ - " . format_date($event->start) ?>
                    </span>
                </div>
                <div class="col-sm-6">
                    <?php include "components/finish_icon.php" ?>
                    <span>
                        <?= "Retour - " . format_date($event->end) ?>
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
                    <?= $event_entry->render("onchange=\"toggleDisplay(this,'eventForm')\"") ?>
                </b>
            </fieldset>
        </header>

        <div id="eventForm" <?= $event_entry->value ?: "class='hidden'" ?>>

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

            <?php if (count($competition_rows)): ?>
                <h4>Courses : </h4>
                <table role="grid">
                    <?php foreach ($competition_rows as $competition_id => $competition): ?>
                        <tr class="display">
                            <td class="competition-name"><b>
                                    <?= $competition['nom'] ?>
                                </b></td>
                            <td class="competition-date">
                                <?= format_date($competition['date']) ?>
                            </td>
                            <td class="competition-place">
                                <?= $competition['lieu'] ?>
                            </td>
                        </tr>
                        <tr class="edit">
                            <td colspan="3">
                                <fieldset class="row">
                                    <?= $competition["entry"]->render("onchange=\"toggleDisplay(this,'competitionForm$competition_id')\"") ?>
                                    <div id="competitionForm<?= $competition_id ?>" <?= $competition['entry']->value ?: " class=hidden" ?>>
                                        <?= $competition["ranked_up"]->render() ?>
                                    </div>
                                    <?= $competition["comment"]->render() ?>
                                </fieldset>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>
        </div>
        <p id="conditionalText">Inscris-toi pour une vraie partie de plaisir !</p>
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