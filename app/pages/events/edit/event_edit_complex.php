<?php
require_once __DIR__ . '/ActivityForm.php';
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false);
$event = $event_id ? em()->find(Event::class, $event_id) : null;

if ($event?->type == EventType::Simple) {
    force_404("This event is a simple event.");
}

if ($event_id) {
    $event = em()->find(Event::class, $event_id);
    if ($event == null) {
        force_404("Error: the event with id $event_id does not exist");
    }
    $event_mapping = [
        "event_name" => $event->name,
        "event_start_date" => date_format($event->start_date, "Y-m-d H:i:s"),
        "event_end_date" => date_format($event->end_date, "Y-m-d H:i:s"),
        "event_limit_date" => date_format($event->deadline, "Y-m-d H:i:s"),
        "event_bulletin_url" => $event->bulletin_url,
        "event_description" => $event->description,
        "event_is_accomodation" => $event->is_accomodation,
        "event_is_transport" => $event->is_transport,
    ];
    foreach ($event->activities as $index => $activity) {
        $event_mapping["activity"][$index] = [
            "id" => $activity->id,
            "name" => $activity->name,
            "type" => $activity->type->value,
            "start_date" => $activity->start_date->format("Y-m-d H:i:s"),
            "end_date" => $activity->end_date->format("Y-m-d H:i:s"),
            "location_label" => $activity->location_label,
            "location_url" => $activity->location_url,
            "description" => $activity->description,
            "deadline" => $activity->deadline->format("Y-m-d H:i:s"),
        ];
        foreach ($activity->categories as $c => $cat) {
            $event_mapping["activity"][$index]["category"][$c] = [
                "id" => $cat->id,
                "name" => $cat->name,
                "toggle" => $cat->removed ? 0 : 1,
            ];
        }
    }
} else {
    $event = new Event();
}

$v = new Validator($event_mapping ?? []);
$event_name = $v->text("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date_time("event_start_date")->label("Date de début")->required();
$end_date = $v->date_time("event_end_date")
    ->label("Date de fin")->required()
    ->min($start_date->value, "Doit être après le début");
$limit_date = $v->date_time("event_limit_date")
    ->label("Deadline")->required()
    ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de départ");
$bulletin_url = $v->url("event_bulletin_url")->label("Lien vers le bulletin")->placeholder();
$description = $v->textarea("event_description")->label("Description");
$is_accomodation = $v->switch("event_is_accomodation")->label("Hébergement");
$is_transport = $v->switch("event_is_transport")->label("Transport");

$activities = $v->collection("activity");
$activity_rows = [];
foreach ($activities as $index => $field_row) {
    $fields = build_activity_validator($field_row, $start_date->value, $end_date->value);
    $categories = $field_row->collection("category");
    $category_id = $categories->hidden("id");
    $category_name = $categories->text("name")->required();
    $category_toggle = $categories->switch("toggle")->set_labels(" ", "Supprimer");
    $activity_rows[$index] = array_merge(
        [
            "field_row" => $field_row,
            "categories" => $categories,
            "category_id" => $category_id,
            "category_name" => $category_name,
            "category_toggle" => $category_toggle,
        ],
        $fields
    );
}

if ($v->valid()) {
    $event->set($event_name->value, $start_date->value, $end_date->value, $limit_date->value, $bulletin_url->value ?? "");
    $event->type = EventType::Complex;
    $event->description = $description->value;
    $event->is_accomodation = $is_accomodation->value ?? false;
    $event->is_transport = $is_transport->value ?? false;
    GroupService::processEventGroupChoice($event);
    em()->persist($event);

    // Process activities
    foreach ($activity_rows as $index => $row) {
        if ($row["id"]->value) {
            $activity = $event->activities->filter(fn($a) => (string) $a->id === (string) $row["id"]->value)->first() ?: null;
            if (!$activity) {
                force_404("Cette activité n'appartient pas à l'événement.");
            }
        } else {
            $activity = new Activity();
        }

        $activity->set(
            $row["name"]->value,
            $row["start_date"]->value,
            $row["end_date"]->value,
            $row["location_label"]->value,
            $row["location_url"]->value,
            $row["description"]->value,
        );
        $activity->type = ActivityType::from($row["type"]->value);
        $activity->deadline = $row["deadline"]->value ? date_create($row["deadline"]->value) : $event->deadline;
        $activity->event = $event;

        // Process categories: rows with an id update that category, rows without are new.
        foreach ($row["category_name"]->fields() as $c => $name_field) {
            $existing = find_owned_category($activity, $row["category_id"]->fields()[$c]->value);
            if ($existing) {
                $existing->name = $name_field->value;
                $existing->removed = !($row["category_toggle"]->fields()[$c]->value ?? 0);
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

        em()->persist($activity);
    }

    em()->flush();
    Toast::success("Enregistré");
    redirect("/evenements/$event->id");
}

$action = actions();

if ($event_id) {
    $action->back("/evenements/$event_id", "Annuler", "fas fa-xmark");
} else {
    $action->back("/evenements/nouveau/choix", "Retour");
}

$action->submit($event_id ? "Modifier" : "Créer");

page($event_id ? "{$event->name} : Modifier" : "Créer un événement multi-activité")->enableHelp();
?>
<script src="/assets/js/start-intro.js"></script>
<div id="form-div">
    <form method="post" novalidate>
        <?= $action ?>
        <article class="row">
            <?= $v->render_validation() ?>
            <?php foreach ($activity_rows as $index => $row): ?>
                <?php if (!$v->empty && !$row['field_row']->valid()): ?>
                    <label class="error">
                        <a href="#"
                            onclick="tabsGroup.show('activity-<?= $index ?>'); tabsGroup.scrollIntoView({ behavior: 'smooth', block: 'start' }); return false;">
                            Activité <?= $index + 1 ?> : contient des erreurs
                        </a>
                    </label>
                <?php endif ?>
            <?php endforeach ?>
            <?= $event_name->render() ?>
            <div class="col-sm-6 col-lg-4">
                <?= $start_date->render() ?>
            </div>
            <div class="col-sm-6 col-lg-4">
                <?= $end_date->render() ?>
            </div>
            <div class="col-lg-4" data-intro="Au delà de la deadline, les utilisateurs ne peuvent plus s'inscrire">
                <?= $limit_date->render() ?>
            </div>
            <div data-intro="Vous pouvez ajouer un lien vers un bulletin en ligne"><?= $bulletin_url->render() ?></div>
            <div
                data-intro="Vous pouvez formatter le texte de la description en markdown. N'hésitez pas à aller voir <a href='https://www.markdownguide.org/' target='_blank'>cette ressource</a>">
                <?= $description->attributes(["rows" => "8"])->render() ?>
            </div>
            <?= GroupService::renderEventGroupChoice($event) ?>
            <div data-intro="Activez ou désactivez l'hébergement commun à tout le club sur cet événement...">
                <?= $is_accomodation->render() ?>
            </div>
            <div data-intro="...ainsi que le transport commun.">
                <?= $is_transport->render() ?>
            </div>
        </article>

        <article class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Activités</h2>
                    <button type="button" class="outline" onclick="addActivity()">
                        <i class="fa fa-plus"></i> Ajouter une activité
                    </button>
                </div>
                <sl-tab-group id="activities-tabs">
                    <?php foreach ($activity_rows as $index => $row): ?>
                        <sl-tab slot="nav" panel="activity-<?= $index ?>" id="tab-<?= $index ?>" closable>
                            <?= htmlspecialchars($row['name']->value ?: "Activité " . ($index + 1), ENT_QUOTES) ?>
                        </sl-tab>
                    <?php endforeach ?>

                    <?php foreach ($activity_rows as $index => $row):
                        $panel_categories = $row['categories'];
                        $activity_entity = $event->activities[$index] ?? null;
                        //filter out bookkeeping keys to keep only the activity fields
                        $panel_fields = array_diff_key($row, array_flip(['field_row', 'categories', 'category_id', 'category_name', 'category_toggle']));
                        $panel_category_rows = [];
                        foreach ($panel_categories as $c => $cat_row) {
                            $cat_entity = $activity_entity?->categories[$c] ?? null;
                            $panel_category_rows[$c] = [
                                'entry_count' => count($cat_entity?->activity_entries ?? []),
                            ];
                        }
                        ?>
                        <sl-tab-panel name="activity-<?= $index ?>" id="panel-<?= $index ?>">
                            <?php render_activity_form($panel_fields, $panel_category_rows, $panel_categories, $row['field_row'], true, false, $index, $event); ?>
                        </sl-tab-panel>
                    <?php endforeach ?>
                    <?php if (count($activity_rows) === 0): ?>
                        <div id="no-activity-message" style="padding: 2rem; text-align: center;">
                            <p>Aucune activité. Cliquez sur "Ajouter une activité" pour commencer.</p>
                        </div>
                    <?php endif ?>
                </sl-tab-group>
            </div>
        </article>
    </form>
</div>

<script>
    var activityCount = <?= count($activity_rows) ?>;
    var tabsGroup = document.getElementById('activities-tabs');

    function addActivity() {
        // Hide the "no activity" message if it exists
        const noActivityMessage = document.getElementById('no-activity-message');
        if (noActivityMessage) {
            noActivityMessage.style.display = 'none';
        }

        // Create new tab
        const newTab = document.createElement('sl-tab');
        newTab.slot = 'nav';
        newTab.panel = `activity-${activityCount}`;
        newTab.id = `tab-${activityCount}`;
        newTab.closable = true;
        newTab.textContent = `Activité ${activityCount + 1}`;

        // Add close event listener
        newTab.addEventListener('sl-close', (e) => {
            e.preventDefault();
            const index = parseInt(newTab.id.replace('tab-', ''));
            removeActivity(index);
        });

        // Insert at the end of tabs
        const navSlot = tabsGroup.querySelector('[slot="nav"]')?.parentElement || tabsGroup;
        navSlot.appendChild(newTab);

        // Create new panel
        const newPanel = document.createElement('sl-tab-panel');
        newPanel.name = `activity-${activityCount}`;
        newPanel.id = `panel-${activityCount}`;

        const wrapper = document.createElement('div');
        wrapper.id = `activity-wrapper-${activityCount}`;
        wrapper.setAttribute('hx-get', '/evenements/activity_form/<?= $event_id ?? "new" ?>');
        wrapper.setAttribute('hx-trigger', 'load');
        wrapper.setAttribute('hx-swap', 'outerHTML');
        wrapper.setAttribute('hx-vals', JSON.stringify({
            action: activityCount,
            is_new: '1',
            event_start_date: document.querySelector('input[name="start_date"]')?.value ?? '',
            event_end_date: document.querySelector('input[name="end_date"]')?.value ?? '',
        }));

        newPanel.appendChild(wrapper);
        tabsGroup.appendChild(newPanel);

        htmx.process(wrapper);

        // Switch to new tab after HTMX loads content
        const currentActivityIndex = activityCount;
        const activateTabHandler = function (event) {
            if (event.detail.target.id === `activity-wrapper-${currentActivityIndex}`) {
                tabsGroup.show(`activity-${currentActivityIndex}`);
                newTab.click();
                document.body.removeEventListener('htmx:afterSwap', activateTabHandler);
            }
        };
        document.body.addEventListener('htmx:afterSwap', activateTabHandler);

        activityCount++;
    }

    function removeActivity(index) {
        const tab = document.getElementById(`tab-${index}`);
        const panel = document.getElementById(`panel-${index}`);

        if (tab && panel) {
            const activityId = document.querySelector(`input[name="activity[${index}][id]"]`)?.value;

            // If activity has an ID - redirect to delete page
            if (activityId) {
                window.location.href = `/evenements/<?= $event_id ?>/activite/${activityId}/supprimer?return=<?= urlencode("/evenements/$event_id/modifier/complexe") ?>`;
                return;
            }

            // Otherwise, just remove the new unsaved activity from DOM
            // Switch to first tab before removing
            const firstTab = tabsGroup.querySelector('sl-tab');
            if (firstTab && firstTab.id !== `tab-${index}`) {
                tabsGroup.show(firstTab.panel);
            }

            tab.remove();
            panel.remove();

            // Show "no activity" message if no tabs remaining
            const remainingTabs = tabsGroup.querySelectorAll('sl-tab');
            if (remainingTabs.length === 0) {
                const noActivityMessage = document.getElementById('no-activity-message');
                if (noActivityMessage) {
                    noActivityMessage.style.display = 'block';
                }
            }
        }
    }

    function addCategoryToActivity(activityIndex) {
        const categoriesDiv = document.getElementById(`activity_${activityIndex}_categories`);
        const nextIndex = categoriesDiv.querySelectorAll('.category-row').length;

        const row = document.createElement("div");
        row.className = "category-row";
        row.innerHTML = `<input name="activity[${activityIndex}][category][${nextIndex}][name]" placeholder="Entrer le nom de la catégorie" required>`
            + `<input type="hidden" name="activity[${activityIndex}][category][${nextIndex}][toggle]" value="1">`;

        categoriesDiv.appendChild(row);
    }

    // Add close event listeners to existing tabs
    document.querySelectorAll('sl-tab[closable]').forEach(tab => {
        tab.addEventListener('sl-close', (e) => {
            e.preventDefault();
            const index = parseInt(tab.id.replace('tab-', ''));
            removeActivity(index);
        });
    });

    // Update tab name when activity name changes
    document.addEventListener('input', (e) => {
        if (e.target.name && e.target.name.match(/^activity\[(\d+)\]\[name\]$/)) {
            const match = e.target.name.match(/^activity\[(\d+)\]\[name\]$/);
            if (match) {
                const index = match[1];
                const tab = document.getElementById(`tab-${index}`);
                if (tab) {
                    tab.textContent = e.target.value || 'Activité ' + (parseInt(index) + 1);
                }
            }
        }
    });
</script>