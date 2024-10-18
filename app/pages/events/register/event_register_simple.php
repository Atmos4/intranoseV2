<?php

$user = User::getCurrent();
$event = EventService::getEventWithAllData(get_route_param('event_id'), $user->id);

if (!check_auth(Access::$ADD_EVENTS) && (!$event->open || $event->deadline < date_create("today"))) {
    force_404("this event is closed for entry");
}

$event_entry = $event->entries[0] ?? null;
if (!$event->activities) {
    return "there is no activity associated with this event";
}
$activity = $event->activities[0];
$activity_entry = $activity->entries[0];

if ($activity_entry) {
    $form_values = [
        "activity_entry" => $activity_entry->present,
        "activity_comment" => $activity_entry->comment,
        "activity_category" => $activity_entry->category->id ?? ""
    ];
}

$v = new Validator($form_values ?? []);
$activity_present = $v->switch("activity_entry")->set_labels("Je participe", "Je ne participe pas");
$activity_comment = $v->textarea("activity_comment")->label("Remarque");
if (count($activity->categories)) {
    $activity_category = $v->select("activity_category")->label("Catégorie")
        ->options(Category::toSelectOptions($activity->categories));
}

if ($v->valid()) {
    $event_entry ??= new EventEntry();
    $event_entry->set(
        $user,
        $event,
        $activity_present->value,
        false,
        false,
        date_create(),
        "",
    );
    em()->persist($event_entry);

    // Map activity categories with ids
    $activity_category_map = [];
    foreach ($activity->categories as $category) {
        $activity_category_map[$category->id] = $category;
    }

    $activity_entry = $activity->entries[0] ?? new ActivityEntry();
    $activity_present = $activity_present->value;
    if ($activity_present) {
        $activity_entry->set(
            $user,
            $activity,
            $activity_present,
            $activity_present ? $activity_comment->value : "",
        );
        $activity_entry->category = $activity_present ? $activity_category_map[$activity_category->value] : null;
        em()->persist($activity_entry);
        em()->flush();
        redirect("/evenements/$event->id");
    }
}

function getToggleClass($selector, $initialState)
{
    return $selector . ($initialState ? "" : " hidden");
}

page("Inscription - " . $event->name)->css("event_register.css");
?>
<form id="mainForm" method="post">
    <?= actions()->back("/evenements/$event->id")->submit("Enregistrer") ?>
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
                <div>
                    <i class="fas fa-clock"></i>
                    <span>
                        <?= "Date limite - " . format_date($event->deadline) ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="row">
            <div class="col-sm-12 col-md-6">
                <?= $activity_present->attributes(["onchange" => "toggleDisplay(this,'.activityToggle')"])->render() ?>
                <?php $toggle_class = getToggleClass("activityToggle", $activity_present->value); ?>
            </div>
            <div class="col-sm-12 col-md-6 <?= $toggle_class ?>">
                <?php if (count($activity->categories)): ?>
                    <?= $activity_category->render() ?>
                <?php endif ?>
            </div>
            <div class="col-12 <?= $toggle_class ?>">
                <?= $activity_comment->render() ?>
            </div>
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