<?php
restrict_access();

include __DIR__ . "/eventUtils.php";

$event = Event::getWithGraphData(get_route_param('event_id'), User::getCurrent()->id);
if (!$event->open) {
    restrict_access(Access::$ADD_EVENTS);
}

$can_edit = check_auth(Access::$ADD_EVENTS);
$entry = $event->entries->get(0) ?? null;
$totalEntryCount = EventService::getEntryCount($event->id);

page($event->name)->css("event_view.css");
?>
<nav id="page-actions">
    <a href="/evenements" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>

    <?php if ($can_edit): ?>
        <li>
            <details class="dropdown">
                <summary>Actions</summary>
                <ul dir="rtl">
                    <li><a href="/evenements/<?= $event->id ?>/modifier" class="secondary">
                            <i class="fas fa-pen"></i> Éditer
                        </a></li>
                    <li>
                        <?php if (!$event->open): ?>
                            <a href="/evenements/<?= $event->id ?>/supprimer" class="destructive">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        <?php elseif ($event->open): ?>
                            <a href="/evenements/<?= $event->id ?>/publier" class="destructive">
                                <i class="fas fa-calendar-minus"></i> Retirer
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </details>
        </li>

    <?php endif ?>
</nav>

<?php if (!$event->open): ?>
    <article class="entry-summary entry-header">
        Cet évenement n'est pas publié
        <a href="/evenements/<?= $event->id ?>/publier" class="outline contrast">
            <i class="fas fa-paper-plane"></i> Publier
        </a>
    </article>
<?php endif ?>


<article class="entry-summary <?= $entry?->present ? "entered" : "not-entered" ?>">
    <header class="entry-header">
        <b>
            <?= match (true) {
                !$entry => IconText("fa-question", "Pas encore inscrit", "span"),
                !$entry->present => IconText("fa-xmark", "Je ne participe pas", "del"),
                $entry->present => IconText("fa-check", "Inscrit", "ins"),
                default => "Erreur"
            } ?>
        </b>
        <?php if (($event->open && $event->deadline >= date_create("today")) || $can_edit): ?>
            <a href="/evenements/<?= $event->id ?>/inscription" class="outline contrast">
                <i class="fas fa-pen-to-square"></i> <?= $entry ? "Gérer l'inscription" : "S'inscrire" ?>
            </a>
        <?php endif ?>
    </header>
    <div class="row g-2">
        <?php if ($entry && $entry->present): ?>
            <div class="col-12 col-md-6">
                <?= ConditionalIcon($entry->transport, "Transport avec le club") ?>
            </div>
            <div class="col-12 col-md-6">
                <?= ConditionalIcon($entry->accomodation, "Hébergement avec le club") ?>
            </div>
        <?php endif ?>
        <?php if ($entry && $entry->comment): ?>
            <div class="col-12">
                <i class="fa fa-comment fa-fw"></i>
                <span class="space-before"><?= $entry->comment ?></span>
            </div>
        <?php endif; ?>
    </div>
</article>

<article>
    <header>
        <div class="row g-2 center align-center">
            <div class="col-sm-12 col-md">
                <?php include app_path() . "/components/start_icon.php" ?>
                <span>
                    <?= "Départ : " . format_date($event->start_date) ?>
                </span>
                <br>
                <?php include app_path() . "/components/finish_icon.php" ?>
                <span>
                    <?= "Retour : " . format_date($event->end_date) ?>
                </span>
                <br>
                <i class="fas fa-clock"></i>
                <span>
                    <?= "Date limite : " . format_date($event->deadline) ?>
                </span>
            </div>
            <div class="col-auto">
                <div class="row g-2">
                    <?php if ($event->bulletin_url): ?>
                        <a role="button" href="<?= $event->bulletin_url ?>" target="_blank" class="secondary"> <i
                                class="fa fa-paperclip"></i>
                            Bulletin
                            <i class="fa fa-external-link"></i></a>
                    <?php endif ?>
                    <?php if ($event->open && $totalEntryCount): ?>
                        <a role="button" href="/evenements/<?= $event->id ?>/participants" class="secondary">
                            <i class="fas fa-users"></i> Participants
                            <?= "($totalEntryCount)" ?>
                        </a>
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
                    <?= ActivityEntry($activity_entry) ?>
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
                    <p>
                        <a role="button" class="outline secondary"
                            href='/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>'>
                            <i class="fa fa-circle-info"></i>
                            Détails</a>
                        <?php if ($can_edit): ?>
                            <a role="button" class="outline secondary"
                                href='/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>/modifier'>
                                <i class="fa fa-pen"></i>
                                Modifier</a>
                        <?php endif ?>
                    </p>
                </details>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>


        <?php if ($can_edit): ?>
            <p>
                <a role=button class="secondary" href="/evenements/<?= $event->id ?>/ajouter-activite">
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

<?= UserModal::renderRoot() ?>