<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false, numeric: false);
if ($event_id) {
    $event = em()->find(Event::class, $event_id);
} else {
    $event = new Event();
}

$post_form_values = isset($_POST["form_values"]) ? json_decode($_POST["form_values"], true) : null;
$is_simple = $_POST["is_simple"] ?? ($event ? $event->type == EventType::Simple : false);
$activity_index = $_POST["action"] ?? null;

// is_complex true only when editing a from complex event form (not a single activity of a complex event)
$is_complex = !$is_simple && $activity_index !== "single_activity";

// Field name prefix for complex events with multiple activities
$prefix = $is_complex ? "activity_{$activity_index}_" : "";

if ($post_form_values) {
    $form_values = [
        $prefix . "name" => $post_form_values["activity_name"] ?? null,
        $prefix . "type" => $post_form_values["activity_type"] ?? null,
        $prefix . "start_date" => $post_form_values["activity_start_date"] ?? null,
        $prefix . "end_date" => $post_form_values["activity_end_date"] ?? null,
        $prefix . "location_label" => $post_form_values["activity_location_label"] ?? null,
        $prefix . "location_url" => $post_form_values["activity_location_url"] ?? null,
        $prefix . "description" => $post_form_values["activity_description"] ?? null,
        $prefix . "deadline" => $post_form_values["activity_deadline"] ?? null,
    ];
    $activity_id = $post_form_values["activity_id"] ?? null;
    $categories = $post_form_values["activity_categories"] ?? [];
    foreach ($categories as $index => $category) {
        $form_values["{$prefix}category_{$index}_name"] = $category["name"];
        $form_values["{$prefix}category_{$index}_toggle"] = $category["removed"] ? 0 : 1;
    }
} else {
    $form_values = $event_id ? ["start_date" => $event->start_date->format("Y-m-d"), "end_date" => $event->end_date->format("Y-m-d")] : [];
    $activity_id = null;
    $categories = [];
}

$item_name = !$is_simple ? "activité" : "événement";

$type_array = ["RACE" => "Course", "TRAINING" => "Entraînement", "OTHER" => "Autre"];

$v = new Validator($form_values ?? ($event_id ? ["start_date" => $event->start_date->format("Y-m-d"), "end_date" => $event->end_date->format("Y-m-d")] : []));
$name = $v->text("{$prefix}name")->label("Nom de l'" . $item_name)->placeholder()->required();
$type = $v->select("{$prefix}type")->options($type_array)->label("Type d'$item_name");
$start_date = $v->date_time("{$prefix}start_date")
    ->label("Date de début")
    ->min(date("Y-m-d H:i:s"), "Dans le futur c'est mieux")
    ->required();
$end_date = $v->date_time("{$prefix}end_date")
    ->label("Date de fin")
    ->min($start_date->value, "Doit être après le départ")
    ->required();
if (!$is_simple && $event_id) {
    $start_date->min($event->start_date->format("Y-m-d H:i:s"), "Doit être après la date de début de l'$item_name", true)
        ->max($event->end_date->format("Y-m-d H:i:s"), "Doit être avant la date de fin de l'$item_name", true);
    $end_date->min($event->start_date->format("Y-m-d H:i:s"), "Doit être après la date de début de l'$item_name", true)
        ->max($event->end_date->format("Y-m-d H:i:s"), "Doit être avant la date de fin de l'$item_name", true);
}
$location_label = $v->text("{$prefix}location_label")->label("Nom du Lieu")->required();
$location_url = $v->url("{$prefix}location_url")->label("URL du lieu");

// Set session ID for image uploads - uses event ID if editing, or 'new' for creation
$session_id = $event_id ? "event-{$event_id}" : "event-new";
if ($is_complex && $activity_id) {
    $session_id = "activity-{$activity_id}";
}

$description = $v->richtext("{$prefix}description")
    ->label("Description de l'" . $item_name)
    ->min_height(200)
    ->session_id($session_id);

$deadline = $v->date_time("{$prefix}deadline")
    ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de l'" . $item_name)
    ->label("Date limite d'inscription");
$category_rows = [];
foreach ($categories as $index => $category) {
    $category_rows[$index]['name'] = $v->text("{$prefix}category_{$index}_name")->required();
    $category_rows[$index]['toggle'] = $v->switch("{$prefix}category_{$index}_toggle")->set_labels(" ", "Supprimer");
    $category_rows[$index]['id'] = $category["id"] ?? null;
}

?>
<?php if ($is_complex): ?>
    <article class="activity-form" data-activity-index="<?= $activity_index ?>"
        id="activity-wrapper-<?= $activity_index ?>">
        <input type="hidden" name="activity_<?= $activity_index ?>_id" value="<?= $activity_id ?>">
        <input type="hidden" name="activity_<?= $activity_index ?>_category_count" value="<?= count($categories) ?>">
    <?php else: ?>
        <article>
        <?php endif; ?>
        <?= $name->render() ?>
        <div class="row">
            <div class="col-md-6" <?= ($is_complex === false || $activity_index === '0') ? ' data-intro="N\'hésitez pas à changer le type d\'' . $item_name . ' !"' : '' ?>>
                <?= $type->render() ?>
            </div>
            <div class="col-md-6">
                <?= $start_date->render() ?>
            </div>
            <div class="col-md-6">
                <?= $end_date->render() ?>
            </div>
            <div class="col-md-6">
                <?= $location_label->render() ?>
            </div>
            <div class="col-md-6">
                <?= $location_url->render() ?>
            </div>
            <?php if ($is_simple): ?>
                <div class="col-md-6" <?= ($is_complex === false || $activity_index === '0') ? ' data-intro="Au delà de la deadline, les utilisateurs ne peuvent plus s\'inscrire"' : '' ?>>
                    <?= $deadline->render() ?>
                </div>
            <?php endif ?>
        </div>
        <?= $description->render() ?>
        <?php if ($is_simple): ?>
            <?= GroupService::renderEventGroupChoice($event) ?>
        <?php endif ?>
        <div class="col-auto">
            <h<?= $is_complex ? '4' : '2' ?>>Catégories</h<?= $is_complex ? '4' : '2' ?>>
        </div>
        <div class="col-auto">
            <button type="button" class="outline contrast"
                onclick="<?= $is_complex ? "addCategoryToActivity($activity_index)" : "addCategory()" ?>"
                <?= ($is_complex === false || $activity_index === '0') ? ' data-intro="Ajoutez des catégories selon vos besoin. H21 ou bien végétarien 😋"' : '' ?>><i class="fa fa-plus"></i>
                Ajouter</button>
        </div>
        <div id="<?= $is_complex ? "activity_{$activity_index}_categories" : "categories" ?>" class="col-12">
            <?php if (count($categories)):
                foreach ($categories as $index => $category):
                    $entry_count = count($category["entries"] ?? []); ?>
                    <?php if ($is_complex): ?>
                        <input type="hidden" name="activity_<?= $activity_index ?>_category_<?= $index ?>_id"
                            value="<?= $category_rows[$index]['id'] ?>">
                    <?php endif; ?>
                    <?= "$entry_count inscrits" ?>
                    <div class="category-row">
                        <?= $category_rows[$index]["name"]->render() ?>
                        <?= $category_rows[$index]["toggle"]->render() ?>
                    </div>
                <?php endforeach;
            endif ?>
        </div>
    </article>
    <?php if (!$is_complex): ?>
        <script>
            function addCategory() {
                const categoriesDiv = document.getElementById("categories");
                const input = document.createElement("input");
                input.name = "new_categories[]";
                input.placeholder = "Entrer le nom de la catégorie";

                categoriesDiv.appendChild(input);
            }
        </script>
    <?php endif; ?>