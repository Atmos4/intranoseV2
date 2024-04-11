<?php
restrict_access();

$user = User::getCurrent();

$activity_id = get_route_param("activity_id");
$event_id = get_route_param("event_id");
$activity = em()->find(Activity::class, $activity_id);
$event = em()->find(Event::class, $event_id);

$event_entry = $event->entries[0] ?? null;
$event_present = $event_entry?->present;
$activity_entry = $activity->entries[0] ?? null;
$form_values = [];


if (!$event_entry || !$event_present || $event->deadline < date_create("today")) {
    force_404("this activity is closed for entry");
}

if ($activity_entry) {
    $form_values["activity_entry"] = $activity_entry->present;
    $form_values["activity_comment"] = $activity_entry->comment;
    $form_values["activity_category"] = $activity_entry->category->id ?? "";
}

$v = new Validator($form_values ?? []);
$entry = $v->switch("activity_entry")->set_labels("Je cours", "Je ne cours pas");
$comment = $v->textarea("activity_comment")->label("Remarque");
if (count($activity->categories)) {
    $form_category = $v->select("activity_category")->label("Catégorie")->options(Category::toSelectOptions($activity->categories));
}

if ($v->valid()) {
    // Map activity categories with ids
    $activity_category_map = [];
    foreach ($activity->categories as $category) {
        $activity_category_map[$category->id] = $category;
    }

    $activity_entry = $activity->entries[0] ?? new ActivityEntry();
    $activity_present = $event_present && $entry->value;
    if ($activity_present) {
        $activity_entry->set(
            $user,
            $activity,
            $activity_present,
            $activity_present ? $comment->value : "",
        );
        $activity_entry->category = $activity_present ? $activity_category_map[$form_category->value] : null;
        em()->persist($activity_entry);
    } else {
        em()->remove($activity_entry);
    }
    em()->flush();
    /* redirect("/evenements/$event_id/activite/$activity_id"); */
}
function getToggleClass($selector, $initialState)
{
    return $selector . ($initialState ? "" : " hidden");
}

$toggle_class = getToggleClass("activityToggle", $entry->value);

page("Inscription - " . $activity->name)->css("event_register.css");
?>
<form id="mainForm" method="post">
    <nav id="page-actions">
        <a href="/evenements/<?= $event->id ?>/activite/<?= $activity_id ?>" class="secondary"><i
                class="fas fa-caret-left"></i> Retour</a>
        <button type="submit" role="button">Enregistrer</button>
    </nav>
    <article>
        <header class="center">
            <?= $v->render_validation() ?>
            <div class="row">
                <div class="col-sm-6">
                    <?php include app_path() . "/components/start_icon.php" ?>

                    <span>
                        <?= "Départ - " . format_date($activity->date) ?>
                    </span>
                </div>
                <div>
                    <i class="fas fa-clock"></i>
                    <span>
                        <?= "Date limite - " . format_date($event->deadline) ?>
                    </span>
                </div>
                <span>
                    <i class="fa fa-location-dot fa-fw"></i>
                    <?php if ($activity->location_url): ?>
                        <a href=<?= $activity->location_url ?> target=”_blank”><?= $activity->location_label ?></a>
                    <?php else: ?>
                        <?= $activity->location_label ?>
                    <?php endif ?>
                </span>
            </div>

            <fieldset>
                <b>
                    <?= $entry->attributes(["onchange" => "toggleDisplay(this,'.activityToggle')"])->render() ?>
                </b>
            </fieldset>
        </header>

        <fieldset class="row  <?= $toggle_class ?>">
            <?php if (count($activity->categories)): ?>
                <div class="col-sm-12 col-md-6 <?= $toggle_class ?>">
                    <?= $form_category->render() ?>
                </div>
            <?php endif ?>
        </fieldset>
        <fieldset class="<?= $toggle_class ?>">
            <?= $comment->render() ?>
        </fieldset>
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