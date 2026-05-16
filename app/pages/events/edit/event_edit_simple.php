<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param("event_id", strict: false);
$event = $event_id ? em()->find(Event::class, $event_id) : null;

if ($event && $event->type == EventType::Complex) {
    force_404("This event is a complex event.");
}

if ($event_id) {
    $activity = $event->activities[0];
} else {
    $event = new Event();
    $activity = new Activity();
}

$v = new Validator([], 'simple');
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

// Build hx_vals for re-rendering the partial.
// After a failed POST ($v->empty === false) use submitted values so the user sees what they typed.
// On initial load use entity values (or is_new for a brand-new event).
if (!$v->empty) {
    $hx_vals = [
        'action' => 'simple',
        'is_simple' => true,
        'name' => $v->value('name'),
        'type' => $v->value('type'),
        'start_date' => $v->value('start_date'),
        'end_date' => $v->value('end_date'),
        'location_label' => $v->value('location_label'),
        'location_url' => $v->value('location_url'),
        'description' => $v->value('description'),
        'deadline' => $v->value('deadline'),
        'category_count' => count($activity->categories),
    ];
    foreach ($activity->categories as $i => $category) {
        $hx_vals["category_{$i}_id"] = $category->id;
        $hx_vals["category_{$i}_name"] = $v->value("category_{$i}_name") ?? $category->name;
        $hx_vals["category_{$i}_toggle"] = $v->value("category_{$i}_toggle") ?? ($category->removed ? 0 : 1);
        $hx_vals["category_{$i}_entry_count"] = count($category->activity_entries ?? []);
    }
} elseif ($event_id) {
    $hx_vals = [
        'action' => 'simple',
        'is_simple' => true,
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
    $hx_vals = ['action' => 'simple', 'is_simple' => true, 'is_new' => '1'];
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
        hx-vals='<?= htmlspecialchars(json_encode($hx_vals), ENT_QUOTES, 'UTF-8') ?>'>
    </div>
</form>
<?php if ($event_id): ?>
    <a href="/evenements/<?= $event_id ?>/type" type="button" class="secondary">Changer de type d'événement
        <sl-tooltip content="Vous pouvez passer à un événement complexe pour avoir plusieurs activités"><i
                class="fas fa-circle-info"></i></sl-tooltip></a>
<?php endif ?>