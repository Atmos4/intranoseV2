<?php
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

$form_values = [];
if ($activity_id) {
    $activity = em()->find(Activity::class, $activity_id ?? $event->activities[0]);
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
        hx-vals='{"form_values" : <?= json_encode($form_values) ?>, "action" : "single_activity"}'>
    </div>
</form>