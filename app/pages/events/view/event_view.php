<?php
restrict_access();
include __DIR__ . "/../eventUtils.php";
$user_id = User::getCurrent()->id;
$event = EventService::getEventWithAllData(get_route_param('event_id'), $user_id);
if (!$event->open) {
    restrict_access(Access::$ADD_EVENTS);
}

$vehicles = em()->createQuery('SELECT v FROM Vehicle v WHERE v.event = ?1')->setParameter(
    1,
    $event->id
)->getResult();

$can_edit = check_auth(Access::$ADD_EVENTS);
$today_date = date_create("today");
$deadline_in_future = $event->deadline >= $today_date;
$can_register = ($event->open && $deadline_in_future) || $can_edit;
$entry = $event->entries->get(0) ?? null;
$totalEntryCount = EventService::getEntryCount($event->id);
$is_simple = $event->type == EventType::Simple;

page($event->name)->css("event_view.css");

?>

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

<?php if ($is_simple) {
    require __DIR__ . "/ActivityView.php";
    return;
}

$deadline_class = $deadline_in_future ? "" : ($entry?->present ? "completed" : "missed");
$start_class = $event->start_date < $today_date ? $deadline_class : "";
$end_class = $event->end_date < $today_date ? $deadline_class : "";

?>
<article>
    <header>
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
                    <?php if ($event->open && $totalEntryCount): ?>
                        <div class="col-12 col-lg-auto">
                            <a role="button" href="/evenements/<?= $event->id ?>/participants" class="secondary">
                                <i class="fas fa-users"></i> Participants
                                <?= "($totalEntryCount)" ?>
                            </a>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </header>
    <section>
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
                <a role=button class="secondary" href="/evenements/<?= $event->id ?>/activite/nouveau">
                    <i class="fas fa-plus"></i> Ajouter une activité</a>
            </p>
        <?php endif ?>
    </section>
    <?php if ($event->description): ?>
        <br>
        <section>
            <h3>Description</h3>
            <?= (new Parsedown)->text($event->description) ?>
        </section>
    <?php endif ?>
</article>

<h2><i class="fas fa-car"></i> Véhicules</h2>

<?php foreach ($vehicles as $vehicle): ?>
    <div hx-get="/evenements/<?= $event->id ?>/vehicule/<?= $vehicle->id ?>/inscription/<?= $user_id ?>" hx-trigger="load"
        hx-target="this">
    </div>
<?php endforeach ?>

<a role=button class="secondary" href="/evenements/<?= $event->id ?>/vehicule/nouveau">
    <i class="fas fa-plus"></i> Ajouter un véhicule</a>

<?= UserModal::renderRoot() ?>