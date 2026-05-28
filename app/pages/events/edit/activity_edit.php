<?php
require_once __DIR__ . '/ActivityForm.php';
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

// Build form_values: entity data pre-populates on GET; on POST the Validator reads $_POST instead
$form_values = [];
if ($activity_id) {
    $activity = em()->find(Activity::class, $activity_id);
    $form_values = [
        'name' => $activity->name,
        'type' => $activity->type->value,
        'start_date' => date_format($activity->start_date, "Y-m-d H:i:s"),
        'end_date' => date_format($activity->end_date, "Y-m-d H:i:s"),
        'location_label' => $activity->location_label,
        'location_url' => $activity->location_url,
        'description' => $activity->description,
        'deadline' => date_format($activity->deadline, "Y-m-d H:i:s"),
    ];
    foreach ($activity->categories as $i => $cat) {
        $form_values["category_{$i}_name"] = $cat->name;
        $form_values["category_{$i}_toggle"] = $cat->removed ? 0 : 1;
        $form_values["category_{$i}_entry_count"] = count($cat->activity_entries ?? []);
    }
} else {
    $activity = new Activity();
}

$v = new Validator($form_values, 'single_activity');
$fields = build_activity_validator($v, $event->start_date->format("Y-m-d H:i:s"), $event->end_date->format("Y-m-d H:i:s"));
$name = $fields["name"];
$type = $fields["type"];
$start_date = $fields["start_date"];
$end_date = $fields["end_date"];
$location_label = $fields["location_label"];
$location_url = $fields["location_url"];
$description = $fields["description"];
$deadline = $fields["deadline"];
$categories = [];
$category_rows = [];
foreach ($activity->categories as $index => $category) {
    $categories[$index] = [
        'id' => $category->id,
        'entry_count' => count($category->activity_entries ?? []),
    ];
    $category_rows[$index]['name'] = $v->text("category_{$index}_name");
    $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle");
    $category_rows[$index]['id'] = $category->id;
    $category_rows[$index]['entry_count'] = count($category->activity_entries ?? []);
}

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
    <?php render_activity_form($fields, $category_rows, $categories, $v, false, false, null, $activity->id ?? null, $event); ?>
</form>