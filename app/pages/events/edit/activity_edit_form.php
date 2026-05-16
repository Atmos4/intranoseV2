<?php
require_once __DIR__ . '/activity_validators.php';
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false, numeric: false);
if ($event_id) {
    $event = em()->find(Event::class, $event_id);
} else {
    $event = new Event();
}

$is_simple = $_POST["is_simple"] ?? ($event ? $event->type == EventType::Simple : false);
$activity_index = $_POST["action"] ?? null;

// is_complex true only when editing a from complex event form (not a single activity of a complex event)
$is_complex = !$is_simple && $activity_index !== "single_activity";

// Field name prefix for complex events with multiple activities
$prefix = $is_complex ? "activity_{$activity_index}_" : "";

// Build categories from flat POST fields
$category_count = (int) ($_POST["{$prefix}category_count"] ?? 0);
$categories = [];
for ($i = 0; $i < $category_count; $i++) {
    $categories[$i] = [
        'id' => $_POST["{$prefix}category_{$i}_id"] ?? null,
        'entry_count' => (int) ($_POST["{$prefix}category_{$i}_entry_count"] ?? 0),
    ];
}

$activity_id = $_POST["{$prefix}id"] ?? null;

$item_name = !$is_simple ? "activité" : "événement";

// Validator auto-reads all of $_POST and sets $v->empty = false when action matches
$v = new Validator([], $_POST['action'] ?? null);

// Suppress validation for brand-new (unsaved) activities to avoid errors on initial load
if ($_POST['is_new'] ?? false) {
    $v->empty = true;
}

$event_start = $_POST["event_start_date"] ?? ($event?->id ? $event->start_date->format("Y-m-d H:i:s") : null);
$event_end = $_POST["event_end_date"] ?? ($event?->id ? $event->end_date->format("Y-m-d H:i:s") : null);
$fields = build_activity_validator($v, $event_start, $event_end, $is_complex ? (int) $activity_index : null);
$name = $fields["name"];
$type = $fields["type"];
$start_date = $fields["start_date"];
$end_date = $fields["end_date"];
$location_label = $fields["location_label"];
$location_url = $fields["location_url"];
$description = $fields["description"];
$deadline = $fields["deadline"];
$category_rows = [];
foreach ($categories as $index => $cat) {
    $category_rows[$index]['name'] = $v->text("{$prefix}category_{$index}_name")->required();
    $category_rows[$index]['toggle'] = $v->switch("{$prefix}category_{$index}_toggle")->set_labels(" ", "Supprimer");
    $category_rows[$index]['id'] = $cat['id'];
    $category_rows[$index]['entry_count'] = $cat['entry_count'];
}

?>
<?php if ($is_complex): ?>
    <article class="activity-form" data-activity-index="<?= $activity_index ?>"
        id="activity-wrapper-<?= $activity_index ?>">
        <input type="hidden" name="activity_<?= $activity_index ?>_id" value="<?= $activity_id ?>">
        <input type="hidden" name="activity_<?= $activity_index ?>_category_count" value="<?= count($categories) ?>">
        <?php foreach ($v->fields as $field): ?>
            <?php if ($field->error): ?>
                <label for="<?= $field->key ?>" class="error">
                    <?= $field->get_label() ? $field->get_label() . ' : ' : '' ?>             <?= $field->error ?>
                </label>
            <?php endif ?>
        <?php endforeach ?>
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
                    $entry_count = $category_rows[$index]['entry_count']; ?>
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