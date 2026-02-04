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

if ($post_form_values) {
    $form_values = [
        "name" => $post_form_values["activity_name"] ?? null,
        "type" => $post_form_values["activity_type"] ?? null,
        "start_date" => $post_form_values["activity_start_date"] ?? null,
        "end_date" => $post_form_values["activity_end_date"] ?? null,
        "location_label" => $post_form_values["activity_location_label"] ?? null,
        "location_url" => $post_form_values["activity_location_url"] ?? null,
        "description" => $post_form_values["activity_description"] ?? null,
        "deadline" => $post_form_values["activity_deadline"] ?? null,
    ];
    foreach ($post_form_values["activity_categories"] ?? [] as $index => $category) {
        $form_values["category_{$index}_name"] = $category["name"];
        $form_values["category_{$index}_toggle"] = $category["removed"] ? 0 : 1;
    }
} else {
    $form_values = $event_id ? ["start_date" => $event->start_date->format("Y-m-d"), "end_date" => $event->end_date->format("Y-m-d")] : [];
}

$item_name = ($event_id && !$is_simple) ? "activité" : "événement";

$type_array = ["RACE" => "Course", "TRAINING" => "Entraînement", "OTHER" => "Autre"];

$v = new Validator($form_values ?? ($event_id ? ["start_date" => $event->start_date->format("Y-m-d"), "end_date" => $event->end_date->format("Y-m-d")] : []));
$name = $v->text("name")->label("Nom de l'" . $item_name)->placeholder()->required();
$type = $v->select("type")->options($type_array)->label("Type d'$item_name");
$start_date = $v->date_time("start_date")
    ->label("Date de début")
    ->min(date("Y-m-d H:i:s"), "Dans le futur c'est mieux")
    ->required();
$end_date = $v->date_time("end_date")
    ->label("Date de fin")
    ->min($start_date->value, "Doit être après le départ")
    ->required();
if (!$is_simple) {
    $start_date->min($event->start_date->format("Y-m-d H:i:s"), "Doit être après la date de début de l'$item_name", true)
        ->max($event->end_date->format("Y-m-d H:i:s"), "Doit être avant la date de fin de l'$item_name", true);
    $end_date->min($event->start_date->format("Y-m-d H:i:s"), "Doit être après la date de début de l'$item_name", true)
        ->max($event->end_date->format("Y-m-d H:i:s"), "Doit être avant la date de fin de l'$item_name", true);
}
$location_label = $v->text("location_label")->label("Nom du Lieu")->required();
$location_url = $v->url("location_url")->label("URL du lieu");
$description = $v->textarea("description")->label("Description de l'" . $item_name);
$deadline = $v->date_time("deadline")
    ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de l'" . $item_name)
    ->label("Date limite d'inscription");
$category_rows = [];
foreach ($post_form_values["activity_categories"] ?? [] as $index => $category) {
    $category_rows[$index]['name'] = $v->text("category_{$index}_name")->required();
    $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle")->set_labels(" ", "Supprimer");
}

?>
<article class="row">
    <?= $name->render() ?>
    <div class="col-md-6" data-intro="N'hésitez pas à changer le type d'événement !">
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
        <div class="col-md-6" data-intro="Au delà de la deadline, les utilisateurs ne peuvent plus s'inscrire">
            <?= $deadline->render() ?>
        </div>
    <?php endif ?>
    <?= $description->render() ?>
    <?php if ($is_simple): ?>
        <?= GroupService::renderEventGroupChoice($event) ?>
    <?php endif ?>
    <div class="col-auto">
        <h2>Catégories</h2>
    </div>
    <div class="col-auto">
        <button type="button" class="outline contrast" onclick="addCategory()"
            data-intro="Ajoutez des catégories selon vos besoin. H21 ou bien végétarien 😋"><i class="fa fa-plus"></i>
            Ajouter</button>
    </div>
    <div id="categories" class="col-12">
        <?php if (count($post_form_values["activity_categories"] ?? [])):
            foreach ($post_form_values["activity_categories"] as $index => $category):
                $entry_count = count($category["entries"]); ?>
                <?= "$entry_count inscrits" ?>
                <div class="category-row">
                    <?= $category_rows[$index]["name"]->render() ?>
                    <?= $category_rows[$index]["toggle"]->render() ?>
                </div>
            <?php endforeach;
        endif ?>
    </div>
</article>
<script>
    function addCategory() {
        const categoriesDiv = document.getElementById("categories");
        const input = document.createElement("input");
        input.name = "new_categories[]";
        input.placeholder = "Entrer le nom de la catégorie";

        categoriesDiv.appendChild(input);
    }
</script>