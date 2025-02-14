<?php
return function ($event_id = null, $activity_id = null, bool $is_simple = false, $post_link = "") {
    if ($event_id) {
        $event = em()->find(Event::class, $event_id);
    } else {
        // the only way this function is called with no event_id is from the creation of a simple event
        $event = new Event();
        $event->type = EventType::Simple;
    }

    if ($event_id && !$event) {
        return "this event does not exist";
    }

    if ($is_simple && $event?->type == EventType::Complex) {
        ?>
        <article class="notice invalid">
            Impossible de passer d'un événement complexe à un événement simple
        </article>
        <?php
        return;
    }

    //handle the case when you EDIT an activity OR a simple event
    if ($activity_id || ($event->type == EventType::Simple && $event_id)) {
        $activity = em()->find(Activity::class, $activity_id ?? $event->activities[0]);
        $form_values = [
            "name" => $activity->name,
            "date" => date_format($activity->date, "Y-m-d"),
            "location_label" => $activity->location_label,
            "location_url" => $activity->location_url,
            "description" => $activity->description,
            "deadline" => date_format($activity->deadline, "Y-m-d"),
        ];
        foreach ($activity->categories as $index => $category) {
            $form_values["category_{$index}_name"] = $category->name;
            $form_values["category_{$index}_toggle"] = $category->removed ? 0 : 1;
        }
    } else {
        $activity = new Activity();
    }

    $item_name = ($event_id && !($event->type == EventType::Simple)) ? "activité" : "événement";

    $type_array = ["RACE" => "Course", "TRAINING" => "Entraînement", "OTHER" => "Autre"];

    $v = new Validator($form_values ?? ($event_id ? ["date" => $event->start_date->format("Y-m-d")] : []));
    $name = $v->text("name")->label("Nom de l'" . $item_name)->placeholder()->required();
    $type = $v->select("type")->options($type_array)->label("Type d'$item_name");
    $date = $v->date("date")
        ->label("Date")
        ->min(date("Y-m-d"), "Dans le futur c'est mieux");
    if (!$is_simple) {
        $date->min($event->start_date->format("Y-m-d"), "Doit être après la date de début de l'$item_name", true)
            ->max($event->end_date->format("Y-m-d"), "Doit être avant la date de fin de l'$item_name", true);
    }
    $date->required();
    $location_label = $v->text("location_label")->label("Nom du Lieu")->required();
    $location_url = $v->url("location_url")->label("URL du lieu");
    $description = $v->textarea("description")->label("Description de l'" . $item_name);
    $deadline = $v->date("deadline")
        ->max($date->value ? date_create($date->value)->format("Y-m-d") : "", "Doit être avant le jour de l'" . $item_name)
        ->label("Date limite d'inscription");
    $category_rows = [];
    foreach ($activity->categories as $index => $category) {
        $category_rows[$index]['name'] = $v->text("category_{$index}_name")->required();
        $category_rows[$index]['toggle'] = $v->switch("category_{$index}_toggle")->set_labels(" ", "Supprimer");
    }

    $return_link = match (true) {
        $event_id && $activity_id => "/evenements/$event_id/activite/$activity_id",
        !!$event_id => "/evenements/$event_id",
        default => "/evenements",
    };

    if ($v->valid()) {
        //right now the deadline is the same as the event - always. Can be changed in the future.
        $activity->set($name->value, $date->value, $location_label->value, $location_url->value, $description->value);
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
        // With a simple event, we need to edit the event as well
        if ($is_simple) {
            $event->set($name->value, $date->value, $date->value, $deadline->value, "");
            $event->type = EventType::Simple;
            GoogleCalendarService::updateEvent($event);
            em()->persist($event);
        }
        GroupService::processEventGroupChoice($event);
        $activity->event = $event;
        em()->persist($activity);
        em()->flush();
        Toast::success("Enregistré");
        redirect($return_link);
    }
    ?>
    <form method="post" hx-post=<?= $post_link ?>>
        <?= actions()?->back("/evenements" . ($event_id ? "/$event_id" : ""), "Annuler", " fas fa-xmark")->submit(($activity_id || $event?->type == EventType::Simple) ? "Modifier" : "Créer") ?>
        <article class="row">
            <?= $v->render_validation() ?>
            <?= $name->render() ?>
            <div class="col-md-6" data-intro="N'hésitez pas à changer le type d'événement !">
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
            <?php if ($is_simple): ?>
                <div class="col-md-6" data-intro="Au delà de la deadline, les utilisateurs ne peuvent plus s'inscrire">
                    <?= $deadline->render() ?>
                </div>
            <?php endif ?>
            <?= $description->render() ?>
            <?= GroupService::renderEventGroupChoice($event) ?>
            <div class="col-auto">
                <h2>Catégories</h2>
            </div>
            <div class="col-auto">
                <button type="button" class="outline contrast" onclick="addCategory()"
                    data-intro="Ajoutez des catégories selon vos besoin. H21 ou bien végétarien 😋"><i
                        class="fa fa-plus"></i>
                    Ajouter</button>
            </div>
            <div id="categories" class="col-12">
                <?php if (count($activity->categories)):
                    foreach ($activity->categories as $index => $category):
                        $entry_count = count($category->activity_entries); ?>
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
<?php } ?>