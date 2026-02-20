<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param("event_id", strict: false);
$event = $event_id ? em()->find(Event::class, $event_id) : null;

if ($event && $event->type == EventType::Complex) {
    force_404("This event is a complex event.");
}

$form_values = [];
if ($event_id) {
    $activity = $event->activities[0];
    $form_values = [
        "activity_name" => $activity->name,
        "activity_type" => $activity->type->value,
        "activity_start_date" => date_format($activity->start_date, "Y-m-d H:i:s"),
        "activity_end_date" => date_format($activity->end_date, "Y-m-d H:i:s"),
        "activity_location_label" => $activity->location_label,
        "activity_location_url" => $activity->location_url,
        "activity_description" => $activity->description,
        "activity_deadline" => date_format($activity->deadline, "Y-m-d H:i:s"),
    ];
    $form_values["activity_categories"] = [];
    foreach ($activity->categories as $index => $category) {
        $form_values["activity_categories"][] = [
            "name" => $category->name,
            "removed" => $category->removed,
            "entries" => $category->activity_entries
        ];
    }
} else {
    $event = new Event();
    $activity = new Activity();
}

$v = new Validator();
$name = $v->text("name");
$type = $v->select("type");
$start_date = $v->date_time("start_date");
$end_date = $v->date_time("end_date");
$location_label = $v->text("location_label");
$location_url = $v->url("location_url");
$description = $v->textarea("description");
$deadline = $v->date_time("deadline");
$category_rows = [];
foreach ($activity->categories as $index => $category) {
    $category_rows[$index]['name'] = $v->text("category_{$index}_name");
    $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle");
}

if ($v->valid()) {
    $activity->set($name->value, $start_date->value, $end_date->value, $location_label->value, $location_url->value, $description->value);
    $activity->type = ActivityType::from($type->value);
    $activity->deadline = $deadline->value ? date_create($deadline->value) : date_create($deadline->value);
    foreach ($activity->categories as $index => $category) {
        $category->name = $category_rows[$index]['name']->value;
        $category->removed = !$category_rows[$index]['toggle']->value ?? 0;
        if ($category->removed) {
            em()->remove($category);
            $activity->categories->removeElement($category);
        }
    }
    $new_categories = $_POST["new_categories"] ?? [];
    foreach ($new_categories as $category_name) {
        if ($category_name) {
            $category = new Category();
            $category->name = $category_name;
            $category->activity = $activity;
            $activity->categories[] = $category;
        }
    }
    $event->set($name->value, $start_date->value, $end_date->value, $deadline->value, "");
    $event->type = EventType::Simple;
    GroupService::processEventGroupChoice($event);
    $activity->event = $event;
    em()->persist($event);
    em()->persist($activity);
    em()->flush();
    Toast::success("Enregistré");
    redirect($event_id ? "/evenements/$event_id" : "/evenements");
}

$action = actions();

if ($event_id) {
    $action->back("/evenements/$event_id", "Annuler", "fas fa-xmark");
} else {
    $action->back("/evenements/nouveau/choix", "Retour");
}

$action->submit($event_id ? "Modifier" : "Créer");

page($event_id ? "{$event->name} : Modifier" : "Créer un événement mono-activité")->enableHelp();
?>
<form method="post">
    <?= $action ?>
    <?= $v->render_validation() ?>
    <div id="form-div" hx-post="/evenements/activity_form/<?= $event_id ? $event_id : "new" ?>" hx-trigger="load"
        hx-vals='<?= htmlspecialchars(json_encode(["form_values" => $form_values, "is_simple" => true, "action" => "simple"]), ENT_QUOTES, 'UTF-8') ?>'>
    </div>
</form>
<?php if ($event_id): ?>
    <a href="/evenements/<?= $event_id ?>/type" type="button" class="secondary">Changer de type d'événement
        <sl-tooltip content="Vous pouvez passer à un événement complexe pour avoir plusieurs activités"><i
                class="fas fa-circle-info"></i></sl-tooltip></a>
<?php endif ?>