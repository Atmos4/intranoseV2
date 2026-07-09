<?php
function render_activity_form(
    array $fields,
    array $category_rows,
    array $categories,
    Validator $v,
    bool $is_complex,
    bool $is_simple,
    ?int $activity_index,
    ?string $activity_id,
    Event $event,
): void {
    $name = $fields['name'];
    $type = $fields['type'];
    $start_date = $fields['start_date'];
    $end_date = $fields['end_date'];
    $location_label = $fields['location_label'];
    $location_url = $fields['location_url'];
    $description = $fields['description'];
    $deadline = $fields['deadline'];
    $item_name = $is_simple ? "événement" : "activité";
    ?>
    <?= $v->render_validation() ?>
    <?php if ($is_complex): ?>
        <article class="activity-form" data-activity-index="<?= $activity_index ?>"
            id="activity-wrapper-<?= $activity_index ?>">
            <input type="hidden" name="activity_<?= $activity_index ?>_id" value="<?= $activity_id ?>">
            <input type="hidden" name="activity_<?= $activity_index ?>_category_count" value="<?= count($categories) ?>">
        <?php else: ?>
            <article>
            <?php endif ?>
            <?= $name->render() ?>
            <div class="row">
                <div class="col-md-6" <?= ($is_complex === false || $activity_index === 0) ? ' data-intro="N\'hésitez pas à changer le type d\'' . $item_name . ' !"' : '' ?>>
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
                    <div class="col-md-6" <?= ($is_complex === false || $activity_index === 0) ? ' data-intro="Au delà de la deadline, les utilisateurs ne peuvent plus s\'inscrire"' : '' ?>>
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
                    <?= ($is_complex === false || $activity_index === 0) ? ' data-intro="Ajoutez des catégories selon vos besoin. H21 ou bien végétarien 😋"' : '' ?>>
                    <i class="fa fa-plus"></i> Ajouter
                </button>
            </div>
            <div id="<?= $is_complex ? "activity_{$activity_index}_categories" : "categories" ?>" class="col-12">
                <?php foreach ($categories as $index => $category):
                    $entry_count = $category_rows[$index]['entry_count']; ?>
                    <?php if ($is_complex): ?>
                        <input type="hidden" name="activity_<?= $activity_index ?>_category_<?= $index ?>_id"
                            value="<?= $category_rows[$index]['id'] ?>">
                    <?php endif ?>
                    <?= "$entry_count inscrits" ?>
                    <div class="category-row">
                        <?= $category_rows[$index]['name']->render() ?>
                        <?= $category_rows[$index]['toggle']->render() ?>
                    </div>
                <?php endforeach ?>
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
        <?php endif ?>
        <?php
}

/**
 * Builds and registers all activity fields onto the given Validator instance.
 *
 * This is the single source of truth for activity field definitions and validation
 * constraints. Used by both event_edit_complex.php (POST processing) and
 * activity_edit_form.php (rendering).
 *
 * @param Validator  $v      The validator instance to register fields on.
 * @param string|null $event_start  Event start date string for min/max bounds (submitted or entity value).
 * @param string|null $event_end    Event end date string for min/max bounds (submitted or entity value).
 * @param int|null   $index  Activity index (used to build field name prefix).
 *
 * @return array {
 *   id: mixed,
 *   name: Field,
 *   type: Field,
 *   start_date: DateTimeField,
 *   end_date: DateTimeField,
 *   location_label: Field,
 *   location_url: Field,
 *   description: Field,
 *   deadline: DateTimeField,
 * }
 */
function build_activity_validator(Validator $v, ?string $event_start, ?string $event_end, ?int $index = null): array
{
    $p = !is_null($index) ? "activity_{$index}_" : "";

    $name = $v->text("{$p}name")->label("Nom de l'activité")->placeholder()->required();

    $type_array = ["RACE" => "Course", "TRAINING" => "Entraînement", "OTHER" => "Autre"];
    $type = $v->select("{$p}type")->options($type_array)->label("Type d'activité");

    $start_date = $v->date_time("{$p}start_date")
        ->label("Date de début")
        ->required();

    $end_date = $v->date_time("{$p}end_date")
        ->label("Date de fin")
        ->min($start_date->value, "Doit être après le départ")
        ->required();

    if ($event_start) {
        $start_date
            ->min($event_start, "Doit être après la date de début de l'événement", true)
            ->max($event_end, "Doit être avant la date de fin de l'événement", true);
        $end_date
            ->min($event_start, "Doit être après la date de début de l'événement", true)
            ->max($event_end, "Doit être avant la date de fin de l'événement", true);
    }

    $location_label = $v->text("{$p}location_label")->label("Nom du Lieu")->required();
    $location_url = $v->url("{$p}location_url")->label("URL du lieu");
    $description = $v->textarea("{$p}description")->label("Description de l'activité");

    $deadline = $v->date_time("{$p}deadline")
        ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de l'activité")
        ->label("Date limite d'inscription");

    return [
        "name" => $name,
        "type" => $type,
        "start_date" => $start_date,
        "end_date" => $end_date,
        "location_label" => $location_label,
        "location_url" => $location_url,
        "description" => $description,
        "deadline" => $deadline,
    ];
}
