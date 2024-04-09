<?php
restrict_access();

$user = User::getCurrent();
$event = Event::getWithGraphData(get_route_param('event_id'), $user->id);

if (!check_auth(Access::$ADD_EVENTS) && (!$event->open || $event->deadline < date_create("today"))) {
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

foreach ($event->activities as $index => $activity) {
    $activity_entry = $activity->entries[0] ?? null;
    if ($activity_entry) {
        $form_values["activity_{$index}_entry"] = $activity_entry->present;
        $form_values["activity_{$index}_comment"] = $activity_entry->comment;
        $form_values["activity_{$index}_category"] = $activity_entry->category->id ?? "";
    }
}


$v = new Validator($form_values ?? []);
$event_present = $v->switch("event_present")->set_labels("Je participe", "Pas inscrit");
$transport = $v->switch("event_transport")->label("Transport");
$accomodation = $v->switch("event_accomodation")->label("Hébergement");
$event_comment = $v->textarea("event_comment")->label("Remarques");
$event_comment_noentry = $v->textarea("event_comment_noentry")->label("Remarque");
$activity_rows = [];
foreach ($event->activities as $index => $activity) {
    $activity_rows[$index]["entry"] = $v->switch("activity_{$index}_entry")->set_labels("Je cours", "Je ne cours pas");
    $activity_rows[$index]["comment"] = $v->textarea("activity_{$index}_comment")->label("Remarque");
    if (count($activity->categories)) {
        $activity_rows[$index]["category"] = $v->select("activity_{$index}_category")->label("Catégorie")
            ->options(Category::toSelectOptions($activity->categories));
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

    foreach ($event->activities as $index => $activity) {
        // Map activity categories with ids
        $activity_category_map = [];
        foreach ($activity->categories as $category) {
            $activity_category_map[$category->id] = $category;
        }

        $activity_entry = $activity->entries[0] ?? new ActivityEntry();
        $activity_form = $activity_rows[$index];
        $activity_present = $event_present->value && $activity_form["entry"]->value;
        if ($activity_present) {
            $activity_entry->set(
                $user,
                $activity,
                $activity_present,
                $activity_present ? $activity_form["comment"]->value : "",
            );
            $activity_entry->category = $activity_present ? $activity_category_map[$activity_form["category"]->value] : null;
            em()->persist($activity_entry);
        } else {
            em()->remove($activity_entry);
        }
    }
    em()->flush();
    redirect("/evenements/$event->id");
}

function getToggleClass($selector, $initialState)
{
    return $selector . ($initialState ? "" : " hidden");
}

page("Inscription - " . $event->name)->css("event_register.css");
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
                    <?php include app_path() . "/components/start_icon.php" ?>

                    <span>
                        <?= "Départ - " . format_date($event->start_date) ?>
                    </span>
                </div>
                <div class="col-sm-6">
                    <?php include app_path() . "/components/finish_icon.php" ?>
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
                    <?= $event_present->attributes(["onchange" => "toggleDisplay(this,'.eventForm')"])->render() ?>
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

            <?php if (count($event->activities)): ?>
                <h4>Courses : </h4>
                <table role="grid">
                    <?php foreach ($event->activities as $index => $activity):
                        $activity_form = $activity_rows[$index];
                        $toggle_class = getToggleClass("activityToggle$index", $activity_form['entry']->value); ?>
                        <tr class="display">
                            <td class="activity-name"><b>
                                    <?= $activity->name ?>
                                </b></td>
                            <td class="activity-date">
                                <?= format_date($activity->date) ?>
                            </td>
                            <td class="activity-place">
                                <?php if ($activity->location_url): ?>
                                    <a href=<?= $activity->location_url ?> target=”_blank”><?= $activity->location_label ?></a>
                                <?php else: ?>
                                    <?= $activity->location_label ?>
                                <?php endif ?>
                            </td>
                        </tr>
                        <tr class="edit">
                            <td colspan="3">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div>
                                            <?= $activity_form["entry"]->attributes(["onchange" => "toggleDisplay(this,'.activityToggle$index')"])->render() ?>
                                        </div>
                                    </div>
                                    <?php if (count($activity->categories)): ?>
                                        <div class="col-sm-12 col-md-6 <?= $toggle_class ?>">
                                            <?= $activity_form["category"]->render() ?>
                                        </div>
                                    <?php endif ?>
                                    <div class="col-12 <?= $toggle_class ?>">
                                        <?= $activity_form["comment"]->render() ?>
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