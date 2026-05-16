<?php
require_once __DIR__ . '/activity_validators.php';
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);
$activity_id = get_route_param("activity_id", false);
$event = em()->find(Event::class, $event_id);

if ($activity_id) {
    $activity = em()->find(Activity::class, $activity_id);
    if (!$activity) {
        redirect("/evenements/$event_id/activite/nouveau");
    }
    if ($activity->event && $activity->event->id != $event_id) {
        redirect("/evenements/{$activity->event->id}/activite/$activity_id/modifier");
    }
}

if ($event->type == EventType::Simple) {
    return "Pas d'ajout d'activité possible pour un événement simple";
}

$v = new Validator([], 'single_activity');
$fields = build_activity_validator($v, $event->start_date->format("Y-m-d H:i:s"), $event->end_date->format("Y-m-d H:i:s"));
$name = $fields["name"];
$type = $fields["type"];
$start_date = $fields["start_date"];
$end_date = $fields["end_date"];
$location_label = $fields["location_label"];
$location_url = $fields["location_url"];
$description = $fields["description"];
$deadline = $fields["deadline"];
$category_rows = [];
foreach ($activity->categories as $index => $category) {
    $category_rows[$index]['name'] = $v->text("category_{$index}_name");
    $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle");
}

if (!$v->empty) {
    $hx_vals = [
        'name' => $name->value,
        'type' => $type->value,
        'start_date' => $start_date->value,
        'end_date' => $end_date->value,
        'location_label' => $location_label->value,
        'location_url' => $location_url->value,
        'description' => $description->value,
        'deadline' => $deadline->value,
        'category_count' => count($activity->categories),
    ];
    foreach ($activity->categories as $i => $category) {
        $hx_vals["category_{$i}_id"] = $category->id;
        $hx_vals["category_{$i}_name"] = $v->value("category_{$i}_name") ?? $category->name;
        $hx_vals["category_{$i}_toggle"] = $v->value("category_{$i}_toggle") ?? ($category->removed ? 0 : 1);
        $hx_vals["category_{$i}_entry_count"] = count($category->activity_entries ?? []);
    }
} elseif ($activity_id) {
    $activity = em()->find(Activity::class, $activity_id ?? $event->activities[0]);
    $hx_vals = [
        'name' => $activity->name,
        'type' => $activity->type->value,
        'start_date' => date_format($activity->start_date, "Y-m-d H:i:s"),
        'end_date' => date_format($activity->end_date, "Y-m-d H:i:s"),
        'location_label' => $activity->location_label,
        'location_url' => $activity->location_url,
        'description' => $activity->description,
        'deadline' => date_format($activity->deadline, "Y-m-d H:i:s"),
        'category_count' => count($activity->categories),
    ];
    foreach ($activity->categories as $i => $category) {
        $hx_vals["category_{$i}_id"] = $category->id;
        $hx_vals["category_{$i}_name"] = $category->name;
        $hx_vals["category_{$i}_toggle"] = $category->removed ? 0 : 1;
        $hx_vals["category_{$i}_entry_count"] = count($category->activity_entries ?? []);
    }
} else {
    $activity = new Activity();
    $hx_vals = [
        'is_new' => '1',
    ];
}

$hx_vals += [
    'action' => 'single_activity',
    'event_start_date' => $event->start_date->format("Y-m-d H:i:s"),
    'event_end_date' => $event->end_date->format("Y-m-d H:i:s"),
];

if ($v->valid()) {
    //right now the deadline is the same as the event - always. Can be changed in the future.
    $activity->set($name->value, $start_date->value, $end_date->value, $location_label->value, $location_url->value, $description->value);
    $activity->type = ActivityType::from($type->value);
    $activity->deadline = $deadline->value ? date_create($deadline->value) : $event->deadline;
    foreach ($activity->categories as $index => $category) {
        $category->name = $category_rows[$index]['name']->value;
        $category->removed = !$category_rows[$index]['toggle']->value ?? 0;
        // TODO change this later if we want to deal with soft delete
        if ($category->removed /* && !count($category->entries)*/) {
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
    em()->persist($activity);
    em()->flush();
    Toast::success("Enregistré");
    redirect("/evenements/$event_id");
}

$action = actions()
    ->back("/evenements/$event_id", "Annuler", "fas fa-xmark")
    ->submit(($activity_id || $event_id) ? "Modifier" : "Créer");

page($activity_id ? "{$activity->name} : Modifier" : "Ajouter une activité")->css("activity_edit.css");
?>
<form method="post">
    <?= $action ?>
    <?= $v->render_validation() ?>
    <div id="form-div" hx-post="/evenements/activity_form/<?= $event_id ?>" hx-trigger="load"
        hx-vals="<?= htmlspecialchars(json_encode($hx_vals), ENT_QUOTES, 'UTF-8') ?>">
    </div>
</form>