<?php
restrict_access();
include __DIR__ . "/../eventUtils.php";
$user_id = User::getCurrent()->id;
$event = EventService::getEventWithAllData(get_route_param('event_id'), $user_id);
if (!$event->open) {
    restrict_access(Access::$ADD_EVENTS);
}

$tab = get_query_param("tab", false, false);

$can_edit = check_auth(Access::$ADD_EVENTS);
$today_date = date_create("today");
$deadline_in_future = $event->deadline >= $today_date;
$can_register = ($event->open && $deadline_in_future) || $can_edit;
$entry = $event->entries->get(0) ?? null;
$totalEntryCount = EventService::getEntryCount($event->id);
$is_simple = $event->type == EventType::Simple;

page($event->name)->css("event_view.css")->css("entry_list.css")->css("messages.css")->script("select-table.js")->script("copy-entry-emails.js")->enableHelp();
?>
<script>function start_intro() {
        const tabGroup = document.querySelector('sl-tab-group');
        const intro = introJs();

        intro.onbeforechange((element) => {
            // Find the closest tab-panel that contains the element
            const tabPanel = element.closest('sl-tab-panel');
            if (tabPanel) {
                tabGroup.show(tabPanel.name);
            }
        });

        intro.start();
    }</script>

<?= actions()
    ->back("/evenements")
    ->if($can_edit, fn($b) => $b->dropdown(function ($dropdown) use ($event) {
        $dropdown->link("/evenements/$event->id/modifier", "Éditer", "fa-pen", ["class" => "secondary"]);
        $event->open ?
            $dropdown->link("/evenements/$event->id/publier", "Retirer", "fa-calendar-minus", ["class" => "destructive"]) :
            $dropdown->link("/evenements/$event->id/supprimer", "Supprimer", "fa-trash", ["class" => "destructive"]);
    })) ?>

<?php if (!$event->open): ?>
    <article class="notice horizontal">
        Cet évenement n'est pas publié
        <a href="/evenements/<?= $event->id ?>/publier" class="outline contrast">
            <i class="fas fa-paper-plane"></i> Publier
        </a>
    </article>
<?php endif ?>

<?= $is_simple ? RenderActivityEntry($event->activities[0], $can_register) : RenderEventEntry($entry, $event, $can_edit) ?>

<sl-tab-group>
    <sl-tab slot="nav" panel="information" id="information-tab" <?= $tab ? "" : "active" ?>
        data-intro="Cet onglet contient les informations générales de l'événement">
        Informations
    </sl-tab>
    <sl-tab slot="nav" panel="entry-list" id="entry-list-tab" hx-trigger="load"
        hx-post="/evenements/<?= $event->id ?>/participants<?= $is_simple ? "?is_simple=true" : "" ?>"
        hx-target="#entry-list" <?= ($tab == "participants") ? "active" : "" ?>
        data-intro="Cliquez ici pour accéder aux licenciés déjà inscrits à l'événement">
        Participants
    </sl-tab>
    <?php if (Feature::Carpooling->on()): ?>
        <sl-tab slot="nav" panel="vehicles" hx-trigger="load" hx-post="/evenements/<?= $event->id ?>/vehicules"
            hx-target="#vehicles" <?= ($tab == "vehicules") ? "active" : "" ?> id="vehicles-tab"
            data-intro="L'onglet véhicule permet de gérer le covoiturage">
            Véhicules
        </sl-tab>
    <?php endif ?>
    <sl-tab slot="nav" panel="messages" id="messages-tab" hx-trigger="load"
        hx-post="/evenements/<?= $event->id ?>/messages" hx-target="#messages">
        Messages
    </sl-tab>


    <sl-tab-panel name="information">
        <?php if ($is_simple) {
            require __DIR__ . "/ActivityView.php";
        } else {

            $deadline_class = $deadline_in_future ? "" : ($entry?->present ? "completed" : "missed");
            $start_class = $event->start_date < $today_date ? $deadline_class : "";
            $end_class = $event->end_date < $today_date ? $deadline_class : "";

            ?>
            <article>
                <header>
                    <?= GroupService::renderTags($event->groups, delimiter: $event->groups) ?>
                    <div class="row g-2 center align-center">
                        <div class="col-12">
                            <?= RenderTimeline($event, !!$entry?->present) ?>
                        </div>
                        <div class="col-12">
                            <div class="row g-2 center">
                                <?php if ($event->bulletin_url): ?>
                                    <div class="col-12 col-lg-auto">
                                        <a role="button" href="<?= $event->bulletin_url ?>" target="_blank"> <i
                                                class="fa fa-paperclip"></i>
                                            Bulletin
                                            <i class="fa fa-external-link"></i></a>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </header>
                <section
                    data-intro="Les activités sont le contenu d'un événement. Ce peut être des compétitions, des entraînements ou bien des barbeucs 🍴😉">
                    <?php if (count($event->activities)): ?>
                        <h3>Activités</h3>
                        <?php foreach ($event->activities as $i => $activity):
                            $activity_entry = $activity->entries[0] ?? null; ?>
                            <details>
                                <summary>
                                    <?= ConditionalIcon($activity_entry && $activity_entry->present) . " " ?>
                                    <?= $activity->name ?>
                                    <i class="fa <?= $activity->type->toIcon() ?>" title=<?= $activity->type->toName() ?>></i>
                                </summary>
                                <?= RenderActivityEntry($activity) ?>
                                <p class="grid">
                                    <span><i class="fa fa-calendar fa-fw"></i>
                                        <?= format_date($activity->date) ?>
                                    </span>
                                    <?php if ($activity->location_label): ?>
                                        <span>
                                            <i class="fa fa-location-dot fa-fw"></i>
                                            <?php if ($activity->location_url): ?>
                                                <a href=<?= $activity->location_url ?> target="_blank"><?= $activity->location_label ?></a>
                                            <?php else: ?>
                                                <?= $activity->location_label ?>
                                            <?php endif ?>
                                        </span>
                                    <?php endif ?>
                                </p>
                                <div class="buttons-grid">
                                    <a role="button" class="outline secondary"
                                        href='/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>'>
                                        <i class="fa fa-circle-info"></i>
                                        Détails</a>
                                    <?php if ($can_edit): ?>
                                        <a role="button" class="outline secondary"
                                            href='/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>/modifier'>
                                            <i class="fa fa-pen"></i>
                                            Modifier</a>
                                        <a role="button" class="outline error"
                                            href="/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>/supprimer">
                                            <i class="fa fa-trash"></i>
                                            Supprimer
                                        </a>

                                    <?php endif ?>
                                </div>
                            </details>
                            <hr>
                        <?php endforeach; ?>
                    <?php endif; ?>


                    <?php if ($can_edit): ?>
                        <p>
                            <a role=button class="secondary" href="/evenements/<?= $event->id ?>/activite/nouveau"
                                data-intro="Vous pouvez ajouter des activités à un événement complexe">
                                <i class="fas fa-plus"></i> Ajouter une activité</a>
                        </p>
                    <?php endif ?>
                    <?php if ($event->description): ?>
                        <br>
                        <section>
                            <h3>Description</h3>
                            <?= (new Parsedown)->text($event->description) ?>
                        </section>
                    <?php else: ?>
                        Pas encore de description pour cet événement 🪶
                    <?php endif ?>
                </section>
            </article>
        <?php } ?>
    </sl-tab-panel>
    <sl-tab-panel name="entry-list" id="entry-list"></sl-tab-panel>
    <?php if (Feature::Carpooling->on()): ?>
        <sl-tab-panel name="vehicles" id="vehicles"></sl-tab-panel>
    <?php endif ?>
    <sl-tab-panel name="messages" id="messages"></sl-tab-panel>
</sl-tab-group>

<?= UserModal::renderRoot() ?>