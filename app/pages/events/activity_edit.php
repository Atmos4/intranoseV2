<?php
use Doctrine\Common\Collections\ArrayCollection;

restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$activity_id = get_route_param("activity_id", false);
$event = em()->find(Event::class, $event_id);
if (!$event) {
    return "this event does not exist";
}
if ($activity_id) {
    $activity = em()->find(Activity::class, $activity_id);
    $form_values = [
        "name" => $activity->name,
        "date" => date_format($activity->date, "Y-m-d"),
        "location_label" => $activity->location_label,
        "location_url" => $activity->location_url,
        "description" => $activity->description,
    ];
    foreach ($activity->categories as $index => $category) {
        $form_values["category_{$index}_name"] = $category->name;
        $form_values["category_{$index}_toggle"] = $category->removed ? 0 : 1;
    }
} else {
    $activity = new Activity();
}

$type_array = ["RACE" => "Course", "TRAINING" => "Entraînement", "OTHER" => "Autre"];

$v = new Validator($form_values ?? ["date" => $event->start_date->format("Y-m-d")]);
$name = $v->text("name")->label("Nom de l'activité")->placeholder()->required();
$type = $v->select("type")->options($type_array)->label("Type d'activité");
$date = $v->date("date")
    ->label("Date")
    ->min($event->start_date->format("Y-m-d"), "Doit être après la date de début de l'événement")
    ->max($event->end_date->format("Y-m-d"), "Doit être avant la date de fin de l'événement")
    ->required();
$location_label = $v->text("location_label")->label("Nom du Lieu")->required();
$location_url = $v->url("location_url")->label("URL du lieu");
$description = $v->textarea("description")->label("Description de l'activité");

$category_rows = [];
foreach ($activity->categories as $index => $category) {
    $category_rows[$index]['name'] = $v->text("category_{$index}_name")->required();
    $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle")->set_labels(" ", "Supprimer");
}


if ($v->valid()) {
    $activity->set($name->value, date_create($date->value), $event, $location_label->value, $location_url->value, $description->value);
    $activity->type = ActivityType::from($type->value);
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
    redirect("/evenements/$event->id");
}

page($activity_id ? "{$activity->name} : Modifier" : "Ajouter une activité")->css("activity_edit.css");
?>
<form method="post">
    <nav id="page-actions">
        <a href="/evenements/<?= $event_id ?>" class="secondary">
            <i class="fas fa-xmark"></i> Annuler
        </a>
        <button type="submit">
            <?= $activity_id ? "Modifier" : "Créer" ?>
        </button>
    </nav>
    <article class="row">
        <?= $v->render_validation() ?>
        <?= $name->render() ?>
        <div class="col-md-6">
            <?= $type->render() ?>
        </div>
        <div class="col-md-6">
            <?= $date->render() ?>
        </div>
        <div class="col-md-6">
            <?= $location_label->render() ?>
        </div>
        <div class="col-md-6">
            <?= $location_url->render() ?>
        </div>
        <?= $description->render() ?>
        <div class="col-auto">
            <h2>Catégories</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="outline contrast" onclick="addCategory()"><i class="fa fa-plus"></i>
                Ajouter</button>
        </div>
        <div id="categories" class="col-12">
            <?php if (count($activity->categories)):
                foreach ($activity->categories as $index => $category):
                    $entry_count = count($category->entries); ?>
                    <?= "$entry_count inscrits" ?>
                    <div class="category-row">
                        <?= $category_rows[$index]["name"]->render() ?>
                        <?= $category_rows[$index]["toggle"]->render() ?>
                    </div>
                <?php endforeach;
            endif ?>
        </div>
    </article>
</form>
<script>
    function addCategory() {
        const categoriesDiv = document.getElementById("categories");
        const input = document.createElement("input");
        input.name = "new_categories[]";
        input.placeholder = "Entrer le nom de la catégorie";

        categoriesDiv.appendChild(input);
    }
</script>