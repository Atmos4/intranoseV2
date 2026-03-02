<?php

$user = User::getCurrent();
$event = EventService::getEventWithAllData(get_route_param('event_id'), $user->id);

if ($event->type == EventType::Complex) {
    redirect("/evenements/{$event->id}/inscription");
}

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
    $is_present = $activity_present->value;
    $activity_entry->set(
        $user,
        $activity,
        $is_present ?? false,
        $is_present ? $activity_comment->value : "",
    );
    if (count($activity->categories)) {
        $activity_entry->category = $is_present ? $activity_category_map[$activity_category->value] : null;
    }
    em()->persist($activity_entry);
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
    <?= actions()->back("/evenements/$event->id")->submit("Enregistrer") ?>
    <?= GroupService::RenderGroupsWarning($user, $event); ?>
    <article>
        <header class="center">
            <?= $v->render_validation() ?>
            <div class="row">
                <div class="col-sm-6">
                    <?php include app_path() . "/components/start_icon.php" ?>
                    <span>
                        <?= "Début - " . format_date($event->start_date) ?>
                    </span>
                </div>
                <div class="col-sm-6">
                    <?php include app_path() . "/components/finish_icon.php" ?>
                    <span>
                        <?= "Fin - " . format_date($event->end_date) ?>
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

        <input type="hidden" name="activity_entry" value="<?= $activity_present->value ? '1' : '0' ?>"
            id="activity_entry_hidden">
        <div class="button-group-row">
            <button type="button" onclick="setParticipation(true)"
                aria-pressed="<?= $activity_entry && $activity_present->value ? 'true' : 'false' ?>" id="btn_participe"
                class="<?= ($activity_entry && !$activity_present->value) || !$activity_entry ? 'outline' : '' ?>">
                <i class="fas fa-check"></i>
                Je participe
            </button>
            <button type="button" onclick="setParticipation(false)"
                aria-pressed="<?= $activity_entry && !$activity_present->value ? 'true' : 'false' ?>"
                id="btn_no_participe"
                class="<?= ($activity_entry && $activity_present->value) || !$activity_entry ? 'outline' : '' ?>">
                <i class="fas fa-xmark"></i>
                Je ne participe pas
            </button>
        </div>

        <div class="<?= getToggleClass("activityForm", $activity_present->value) ?>">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <?php if (count($activity->categories)): ?>
                        <?= $activity_category->render() ?>
                    <?php endif ?>
                </div>
                <div class="col-12">
                    <?= $activity_comment->render() ?>
                </div>
            </div>
        </div>
    </article>
</form>

<script>
    function toggleDisplay(toggle, target) {
        const elements = document.querySelectorAll(target);
        const show = toggle.value === '1';
        for (element of elements) {
            if (show) {
                element.classList.remove("hidden");
            } else {
                element.classList.add("hidden");
            }
        }
    }

    function setParticipation(participate) {
        document.getElementById('activity_entry_hidden').value = participate ? '1' : '0';
        toggleDisplay({ value: participate ? '1' : '0' }, '.activityForm');
        updateAriaPressed(participate);
    }

    function updateAriaPressed(participate) {
        const btnYes = document.getElementById('btn_participe');
        const btnNo = document.getElementById('btn_no_participe');
        btnYes.setAttribute('aria-pressed', participate ? 'true' : 'false');
        btnNo.setAttribute('aria-pressed', !participate ? 'true' : 'false');
        btnYes.classList.toggle('outline', !participate);
        btnNo.classList.toggle('outline', participate);
    }
</script>