<?php
require_once __DIR__ . '/ActivityForm.php';
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

$form_values = [];
if ($event_id) {
    $form_values = [
        'name' => $activity->name,
        'type' => $activity->type->value,
        'start_date' => $activity->start_date->format("Y-m-d H:i:s"),
        'end_date' => $activity->end_date->format("Y-m-d H:i:s"),
        'location_label' => $activity->location_label,
        'location_url' => $activity->location_url,
        'description' => $activity->description,
        'deadline' => $activity->deadline->format("Y-m-d H:i:s"),
    ];
    foreach ($activity->categories as $i => $cat) {
        $form_values["category"][$i]["id"] = $cat->id;
        $form_values["category"][$i]["name"] = $cat->name;
        $form_values["category"][$i]["toggle"] = $cat->removed ? 0 : 1;
    }
}


$v = new Validator($form_values, 'simple');
$fields = build_activity_validator($v, $event_id ? $event->start_date->format("Y-m-d H:i:s") : null, $event_id ? $event->end_date->format("Y-m-d H:i:s") : null);
$name = $fields["name"];
$type = $fields["type"];
$start_date = $fields["start_date"];
$end_date = $fields["end_date"];
$location_label = $fields["location_label"];
$location_url = $fields["location_url"];
$description = $fields["description"];
$deadline = $fields["deadline"];
$categories = $v->collection("category");
$category_id = $categories->hidden("id");
$category_name = $categories->text("name");
$category_toggle = $categories->switch("toggle");
$category_rows = [];
foreach ($activity->categories as $index => $category) {
    $category_rows[$index]['entry_count'] = count($category->activity_entries ?? []);
}

if ($v->valid()) {
    $activity->set($name->value, $start_date->value, $end_date->value, $location_label->value, $location_url->value, $description->value);
    $activity->type = ActivityType::from($type->value);
    $activity->deadline = $deadline->value ? date_create($deadline->value) : date_create($deadline->value);
    foreach ($category_name->fields() as $index => $name_field) {
        $existing = find_owned_category($activity, $category_id->fields()[$index]->value);
        if ($existing) {
            $existing->name = $name_field->value;
            $existing->removed = !($category_toggle->fields()[$index]->value ?? 0);
            if ($existing->removed) {
                em()->remove($existing);
                $activity->categories->removeElement($existing);
            }
        } else {
            $category = new Category();
            $category->name = $name_field->value;
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
    <?php render_activity_form($fields, $category_rows, $categories, $v, false, true, null, $event); ?>
</form>
<?php if ($event_id): ?>
    <a href="/evenements/<?= $event_id ?>/type" type="button" class="secondary">Changer de type d'événement
        <sl-tooltip content="Vous pouvez passer à un événement complexe pour avoir plusieurs activités"><i
                class="fas fa-circle-info"></i></sl-tooltip></a>
<?php endif ?>