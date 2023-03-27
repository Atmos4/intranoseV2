<?php
use Doctrine\Common\Collections\ArrayCollection;

restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$race_id = get_route_param("race_id", false);
$event = em()->find(Event::class, $event_id);
if (!$event) {
    return "this event does not exist";
}
if ($race_id) {
    $race = em()->find(Race::class, $race_id);
    $form_values = [
        "name" => $race->name,
        "date" => date_format($race->date, "Y-m-d"),
        "place" => $race->place
    ];
    foreach ($race->categories as $index => $category) {
        $form_values["category_{$index}_name"] = $category->name;
        $form_values["category_{$index}_toggle"] = $category->removed ? 0 : 1;
    }
} else {
    $race = new Race();
}

$v = new Validator($form_values ?? []);
$name = $v->text("name")->label("Nom de la course")->placeholder()->required();
$date = $v->date("date")->label("Date")->required();
$place = $v->text("place")->label("Lieu")->required();
$categories = $v->text("categories")->area()
    ->label("Catégories")
    ->placeholder("Ajouter les différentes catégories séparées par un point-virgule");

$category_rows = [];
foreach ($race->categories as $index => $category) {
    $category_rows[$index]['name'] = $v->text("category_{$index}_name")->required();
    $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle")->set_labels(" ", "Supprimer");
}


if ($v->valid()) {
    $race->set($name->value, date_create($date->value), $place->value, $event);
    foreach ($race->categories as $index => $category) {
        $category->name = $category_rows[$index]['name']->value;
        $category->removed = !$category_rows[$index]['toggle']->value ?? 0;
        // TODO change this later if we want to deal with soft delete
        if ($category->removed /* && !count($category->entries)*/) {
            em()->remove($category);
            $race->categories->removeElement($category);
        }
    }
    $new_categories = $_POST["new_categories"] ?? [];
    foreach ($new_categories as $category_name) {
        if ($category_name) {
            $category = new Category();
            $category->name = $category_name;
            $category->race = $race;
            $race->categories[] = $category;
        }
    }
    em()->persist($race);
    em()->flush();
    redirect("/evenements/$event->id");
}

page($race_id ? "{$race->name} : Modifier" : "Ajouter une course", "race_edit.css");
?>
<form method="post">
    <nav id="page-actions">
        <a href="/evenements/<?= $event_id ?>" class="secondary">
            <i class="fas fa-xmark"></i> Annuler
        </a>
        <div>
            <button type="submit">
                <?= $race_id ? "Modifier" : "Créer" ?>
            </button>
        </div>
    </nav>
    <article class="row">
        <?= $v->render_validation() ?>
        <?= $name->render() ?>
        <div class="col-md-6">
            <?= $date->render() ?>
        </div>
        <div class="col-md-6">
            <?= $place->render() ?>
        </div>
        <div class="col-auto">
            <h2>Catégories</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="outline contrast" onclick="addCategory()"><i class="fa fa-plus"></i>
                Ajouter</button>
        </div>
        <div id="categories" class="col-12">
            <?php if (count($race->categories)):
                foreach ($race->categories as $index => $category):
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