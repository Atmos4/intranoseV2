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
        "start_date" => date_format($event->start_date, "Y-m-d H:i:s"),
        "end_date" => date_format($event->end_date, "Y-m-d H:i:s"),
        "limit_date" => date_format($event->deadline, "Y-m-d H:i:s"),
        "bulletin_url" => $event->bulletin_url,
        "description" => $event->description,
        "is_accomodation" => $event->is_accomodation,
        "is_transport" => $event->is_transport,
    ];
} else {
    $event = new Event();
}

$v = new Validator($event_mapping ?? []);
$event_name = $v->text("event_name")->label("Nom de l'événement")->placeholder()->required();
$start_date = $v->date_time("start_date")->label("Date de début")->required();
$end_date = $v->date_time("end_date")
    ->label("Date de fin")->required()
    ->min($start_date->value, "Doit être après le début");
$limit_date = $v->date_time("limit_date")
    ->label("Deadline")->required()
    ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de départ");
$bulletin_url = $v->url("bulletin_url")->label("Lien vers le bulletin")->placeholder();
$description = $v->textarea("description")->label("Description");
$is_accomodation = $v->switch("is_accomodation")->label("Hébergement");
$is_transport = $v->switch("is_transport")->label("Transport");

$existing_count = $event_id ? count($event->activities) : 0;
$total_activity_count = max($existing_count, $_POST ? intval($_POST["activity_count"] ?? 0) : 0);

// Process activities
$activity_validators = [];

// if submit, handle the potential activities with activity_count
if ($_POST) {
    $activity_count = intval($_POST["activity_count"] ?? 0);
    for ($i = 0; $i < $activity_count; $i++) {
        $activity_id = $_POST["activity_{$i}_id"] ?? null;
        $av = new Validator();
        $av_fields = build_activity_validator($av, $start_date->value, $end_date->value, $i);
        $activity_validators[$i] = array_merge(["id" => $activity_id, "v" => $av], $av_fields);
    }
}

// For inital load - build validators from entity data for existing activities
foreach ($event->activities as $index => $activity) {
    if (!isset($activity_validators[$index])) {
        $entity_data = [
            "activity_{$index}_name" => $activity->name,
            "activity_{$index}_type" => $activity->type->value,
            "activity_{$index}_start_date" => $activity->start_date->format("Y-m-d H:i:s"),
            "activity_{$index}_end_date" => $activity->end_date->format("Y-m-d H:i:s"),
            "activity_{$index}_location_label" => $activity->location_label,
            "activity_{$index}_location_url" => $activity->location_url,
            "activity_{$index}_description" => $activity->description,
            "activity_{$index}_deadline" => $activity->deadline->format("Y-m-d H:i:s"),
        ];
        foreach ($activity->categories as $c => $cat) {
            $entity_data["activity_{$index}_category_{$c}_name"] = $cat->name;
            $entity_data["activity_{$index}_category_{$c}_toggle"] = $cat->removed ? 0 : 1;
        }
        $av = new Validator($entity_data);
        $av_fields = build_activity_validator($av, $event->start_date->format("Y-m-d H:i:s"), $event->end_date->format("Y-m-d H:i:s"), $index);
        $activity_validators[$index] = array_merge(["id" => $activity->id, "v" => $av], $av_fields);
    }
}


if ($v->valid() && all_valid($activity_validators)) {
    $event->set($event_name->value, $start_date->value, $end_date->value, $limit_date->value, $bulletin_url->value ?? "");
    $event->type = EventType::Complex;
    $event->description = $description->value;
    $event->is_accomodation = $is_accomodation->value ?? false;
    $event->is_transport = $is_transport->value ?? false;
    GroupService::processEventGroupChoice($event);
    em()->persist($event);

    // Process activities
    foreach ($activity_validators as $index => $av_fields) {
        if ($av_fields["id"]) {
            $activity = em()->find(Activity::class, $av_fields["id"]);
        } else {
            $activity = new Activity();
        }

        $activity->set(
            $av_fields["name"]->value,
            $av_fields["start_date"]->value,
            $av_fields["end_date"]->value,
            $av_fields["location_label"]->value,
            $av_fields["location_url"]->value,
            $av_fields["description"]->value
        );
        $activity->type = ActivityType::from($av_fields["type"]->value);
        $activity->deadline = $av_fields["deadline"]->value ? date_create($av_fields["deadline"]->value) : $event->deadline;
        $activity->event = $event;

        // Process categories
        $category_count = intval($_POST["activity_{$index}_category_count"] ?? 0);
        for ($c = 0; $c < $category_count; $c++) {
            $category_id = $_POST["activity_{$index}_category_{$c}_id"] ?? null;
            $category_name = $_POST["activity_{$index}_category_{$c}_name"] ?? null;
            $category_removed = !($_POST["activity_{$index}_category_{$c}_toggle"] ?? 0);

            if ($category_id) {
                $category = em()->find(Category::class, $category_id);
                if ($category) {
                    $category->name = $category_name;
                    $category->removed = $category_removed;
                    if ($category_removed) {
                        em()->remove($category);
                        $activity->categories->removeElement($category);
                    }
                }
            }
        }

        $new_categories = $_POST["activity_{$index}_new_categories"] ?? [];
        foreach ($new_categories as $category_name) {
            if ($category_name) {
                $category = new Category();
                $category->name = $category_name;
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

function all_valid($validators)
{
    foreach ($validators as $validator_array) {
        if (!$validator_array["v"]->valid()) {
            return false;
        }
    }
    return true;
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
            <?php foreach ($activity_validators as $index => $av_data): ?>
                <?php if ($_POST && !$av_data['v']->valid()): ?>
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
                    <?php foreach ($event->activities as $index => $activity): ?>
                        <sl-tab slot="nav" panel="activity-<?= $index ?>" id="tab-<?= $index ?>" closable>
                            <?= $activity->name ?: "Activité " . ($index + 1) ?>
                        </sl-tab>
                    <?php endforeach ?>
                    <?php for ($i = $existing_count; $i < $total_activity_count; $i++): ?>
                        <sl-tab slot="nav" panel="activity-<?= $i ?>" id="tab-<?= $i ?>" closable>
                            <?= htmlspecialchars($_POST["activity_{$i}_name"] ?: "Activité " . ($i + 1), ENT_QUOTES) ?>
                        </sl-tab>
                    <?php endfor ?>

                    <?php foreach ($event->activities as $index => $activity):
                        $av_data = $activity_validators[$index];
                        $panel_av = $av_data['v'];
                        //filter out id and validator to keep only the fields
                        $panel_fields = array_diff_key($av_data, array_flip(['id', 'v']));
                        $cat_count = $_POST
                            ? intval($_POST["activity_{$index}_category_count"] ?? count($activity->categories))
                            : count($activity->categories);
                        $panel_categories = [];
                        $panel_category_rows = [];
                        for ($c = 0; $c < $cat_count; $c++) {
                            $cat_entity = $activity->categories[$c] ?? null;
                            $panel_categories[$c] = [
                                'id' => $cat_entity?->id ?? null,
                                'entry_count' => count($cat_entity?->activity_entries ?? []),
                            ];
                            $panel_category_rows[$c]['id'] = $cat_entity?->id;
                            $panel_category_rows[$c]['entry_count'] = count($cat_entity?->activity_entries ?? []);
                            $panel_category_rows[$c]['name'] = $panel_av->text("activity_{$index}_category_{$c}_name")->required();
                            $panel_category_rows[$c]['toggle'] = $panel_av->switch("activity_{$index}_category_{$c}_toggle")->set_labels(" ", "Supprimer");
                        }
                        ?>
                        <sl-tab-panel name="activity-<?= $index ?>" id="panel-<?= $index ?>">
                            <?php render_activity_form($panel_fields, $panel_category_rows, $panel_categories, $panel_av, true, false, $index, $activity->id, $event); ?>
                        </sl-tab-panel>
                    <?php endforeach ?>
                    <?php for ($i = $existing_count; $i < $total_activity_count; $i++):
                        $av_data = $activity_validators[$i];
                        $panel_av = $av_data['v'];
                        $panel_fields = array_diff_key($av_data, array_flip(['id', 'v']));
                        $new_cat_count = intval($_POST["activity_{$i}_category_count"] ?? 0);
                        $panel_categories = [];
                        $panel_category_rows = [];
                        for ($c = 0; $c < $new_cat_count; $c++) {
                            $panel_categories[$c] = ['id' => null, 'entry_count' => 0];
                            $panel_category_rows[$c]['id'] = null;
                            $panel_category_rows[$c]['entry_count'] = 0;
                            $panel_category_rows[$c]['name'] = $panel_av->text("activity_{$i}_category_{$c}_name")->required();
                            $panel_category_rows[$c]['toggle'] = $panel_av->switch("activity_{$i}_category_{$c}_toggle")->set_labels(" ", "Supprimer");
                        }
                        ?>
                        <sl-tab-panel name="activity-<?= $i ?>" id="panel-<?= $i ?>">
                            <?php render_activity_form($panel_fields, $panel_category_rows, $panel_categories, $panel_av, true, false, $i, null, $event); ?>
                        </sl-tab-panel>
                    <?php endfor ?>
                    <?php if ($total_activity_count === 0): ?>
                        <div id="no-activity-message" style="padding: 2rem; text-align: center;">
                            <p>Aucune activité. Cliquez sur "Ajouter une activité" pour commencer.</p>
                        </div>
                    <?php endif ?>
                </sl-tab-group>
            </div>
            <input type="hidden" id="activity-count" name="activity_count" value="<?= $total_activity_count ?>">
        </article>
    </form>
</div>

<script>
    var activityCount = <?= $total_activity_count ?>;
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
        document.getElementById('activity-count').value = activityCount;
    }

    function removeActivity(index) {
        const tab = document.getElementById(`tab-${index}`);
        const panel = document.getElementById(`panel-${index}`);

        if (tab && panel) {
            const activityId = document.querySelector(`input[name="activity_${index}_id"]`)?.value;

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
        const categoryCount = document.querySelector(`input[name="activity_${activityIndex}_category_count"]`);
        const currentCount = parseInt(categoryCount.value);

        const input = document.createElement("input");
        input.name = `activity_${activityIndex}_new_categories[]`;
        input.placeholder = "Entrer le nom de la catégorie";

        categoriesDiv.appendChild(input);
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
        if (e.target.name && e.target.name.match(/^activity_\d+_name$/)) {
            const match = e.target.name.match(/^activity_(\d+)_name$/);
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
<script src="/assets/js/shoelace-forms.js"></script>