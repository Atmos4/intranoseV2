<?php
restrict_access();

$user = User::getCurrent();
$event = Event::getWithGraphData(get_route_param('event_id'), $user->id);

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
        $form_values["race_{$index}_category"] = $race_entry->category->id ?? "";
    }
}


$v = new Validator($form_values ?? []);
$event_present = $v->switch("event_present")->set_labels("Je participe", "Pas inscrit");
$transport = $v->switch("event_transport")->label("Transport");
$accomodation = $v->switch("event_accomodation")->label("Hébergement");
$event_comment = $v->textarea("event_comment")->label("Remarques");
$event_comment_noentry = $v->textarea("event_comment_noentry")->label("Remarque");
$race_rows = [];
foreach ($event->races as $index => $race) {
    $race_rows[$index]["entry"] = $v->switch("race_{$index}_entry")->set_labels("Je cours", "Je ne cours pas");
    $race_rows[$index]["ranked_up"] = $v->switch("race_{$index}_ranked_up")->label("Surclassé");
    $race_rows[$index]["comment"] = $v->textarea("race_{$index}_comment")->label("Remarque");
    if (count($race->categories)) {
        $race_rows[$index]["category"] = $v->select("race_{$index}_category")->label("Catégorie")
            ->options(Category::toSelectOptions($race->categories));
    }
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
        // Map race categories with ids
        $race_category_map = [];
        foreach ($race->categories as $category) {
            $race_category_map[$category->id] = $category;
        }

        $race_entry = $race->entries[0] ?? new RaceEntry();
        $race_form = $race_rows[$index];
        $race_present = $event_present->value && $race_form["entry"]->value;
        if ($race_present) {
            $race_entry->set(
                $user,
                $race,
                $race_present,
                $race_present && $race_form["ranked_up"]->value,
                $user->licence,
                $user->sportident,
                $race_present ? $race_form["comment"]->value : "",
            );
            $race_entry->category = $race_present ? $race_category_map[$race_form["category"]->value] : null;
            em()->persist($race_entry);
        } else {
            em()->remove($race_entry);
        }
    }
    em()->flush();
    redirect("/evenements/$event->id");
}

function getToggleClass($selector, $initialState)
{
    return $selector . ($initialState ? "" : " hidden");
}

page("Inscription - " . $event->name)->css("event_view.css");
?>
<form id="mainForm" method="post">
    <nav id="page-actions">
        <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
        <button type="submit" role="button">Enregistrer</button>
    </nav>
    <article>
        <header class="center">
            <?= $v->render_validation() ?>
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
                    <?= $event_present->render("onchange=\"toggleDisplay(this,'.eventForm')\"") ?>
                </b>
            </fieldset>
        </header>

        <div class="<?= getToggleClass("eventForm", $event_present->value) ?>">

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
                        $race_form = $race_rows[$index];
                        $toggle_class = getToggleClass("raceToggle$index", $race_form['entry']->value); ?>
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
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div>
                                            <?= $race_form["entry"]->render("onchange=\"toggleDisplay(this,'.raceToggle$index')\"") ?>
                                        </div>
                                        <div class="<?= $toggle_class ?>">
                                            <?= $race_form["ranked_up"]->render() ?>
                                        </div>
                                    </div>
                                    <?php if (count($race->categories)): ?>
                                        <div class="col-sm-12 col-md-6 <?= $toggle_class ?>">
                                            <?= $race_form["category"]->render() ?>
                                        </div>
                                    <?php endif ?>
                                    <div class="col-12 <?= $toggle_class ?>">
                                        <?= $race_form["comment"]->render() ?>
                                    </div>
                                </div>
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
        const elements = document.querySelectorAll(target);
        for (element of elements) {
            if (toggle.checked) {
                element.classList.remove("hidden");
            } else {
                element.classList.add("hidden");
            }
        }

    }
</script>