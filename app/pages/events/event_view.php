<?php
restrict_access();

require_once app_path() . "/components/conditional_icon.php";

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

    <?php if ($event->open && $event->deadline >= date_create("today")): ?>
        <a href="/evenements/<?= $event->id ?>/inscription" <?= $entry ? "class=\"contrast\"" : "" ?>>
            <i class="fas fa-pen-to-square"></i> Inscription
        </a>
    <?php elseif (!$event->open): ?>
        <a href="/evenements/<?= $event->id ?>/publier">
            <i class="fas fa-paper-plane"></i> Publier
        </a>
    <?php endif ?>

    <?php if ($can_edit): ?>
        <li>
            <details role="list" dir="rtl">
                <summary role="link" aria-haspopup="listbox" class="contrast">Actions</summary>
                <ul role="listbox">
                    <li><a href="/evenements/<?= $event->id ?>/modifier" class="secondary">
                            <i class="fas fa-pen"></i> Modifier
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
<article>
    <header class="center">
        <div class="row">
            <div>
                <?php include app_path() . "/components/start_icon.php" ?>
                <span>
                    <?= "Départ : " . format_date($event->start_date) ?>
                </span>
            </div>
            <div>
                <?php include app_path() . "/components/finish_icon.php" ?>
                <span>
                    <?= "Retour : " . format_date($event->end_date) ?>
                </span>
            </div>
            <div>
                <i class="fas fa-clock"></i>
                <span>
                    <?= "Date limite : " . format_date($event->deadline) ?>
                </span>
            </div>
        </div>

        <div class="row">
            <?php if ($event->open): ?>
                <?php if ($entry): ?>
                    <?php if ($entry->present): ?>
                        <ins><i class="fas fa-check fa-lg"></i></ins>
                    <?php else: ?>
                        <del><i class="fas fa-xmarkf fa-lg"></i></del>
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-question fa-lg"></i>
                <?php endif; ?>
            <?php else: ?>
                <del>Pas encore publié</del>
            <?php endif ?>
        </div>

        <?php if ($event->bulletin_url): ?>

            <p>
                <a href="<?= $event->bulletin_url ?>" target="_blank"> <i class="fa fa-paperclip"></i> Bulletin
                    <i class="fa fa-external-link"></i></a>
            </p>
        <?php endif ?>

        <?php if ($event->open && $totalEntryCount): ?>
            <a role="button" href="/evenements/<?= $event->id ?>/participants" class="secondary">
                <i class="fas fa-users"></i> Participants
                <?= "($totalEntryCount)" ?>
            </a>
        <?php endif ?>
    </header>

    <?php if ($event->open): ?>
        <blockquote>
            <h6>Inscription</h6>
            <p class="row">
                <?php if ($entry?->present): ?>
                    <ins><i class="fas fa-check"></i>
                        <span>Je participe</span></ins>
                <?php else: ?>
                    <del><i class="fas fa-xmark"></i>
                        <span>
                            <?= $entry ? "Je ne participe pas" : "Pas inscrit" ?>
                        </span></del>
                <?php endif; ?>
            </p>
            <?php if ($entry && $entry->present): ?>
                <div class="grid">
                    <p>
                        <?= ConditionalIcon($entry->transport, "Transport avec le club") ?>
                    </p>
                    <p>
                        <?= ConditionalIcon($entry->accomodation, "Hébergement avec le club") ?>
                    </p>
                </div>
            <?php endif ?>

            <?php if ($entry && $entry->comment): ?>
                <cite>Remarque : </cite>
                <?= $entry->comment ?>
            <?php endif; ?>
        </blockquote>
    <?php endif ?>

    <?php if (count($event->activities)): ?>
        <h3>Activités :
        </h3>

        <?php foreach ($event->activities as $activity):
            if ($activity->type == ActivityType::RACE) {
                $icon = "fa-stopwatch";
            } elseif ($activity->type == ActivityType::TRAINING) {
                $icon = "fa-dumbbell";
            } else {
                $icon = "fa-bowl-food";
            }
            $activity_entry = $activity->entries[0] ?? null; ?>
            <details>
                <summary>
                    <i class="fa <?= $icon ?>"></i>
                    <?= ConditionalIcon($activity_entry && $activity_entry->present) . " " ?>
                    <b>
                        <?= $activity->name ?>
                    </b>
                </summary>
                <h6>Description</h6>
                <div class="grid">
                    <ul class="fa-ul">
                        <li><span class="fa-li"><i class="fa fa-calendar"></i></span>
                            <?= format_date($activity->date) ?>
                        </li>
                    </ul>
                    <ul class="fa-ul">
                        <li><span class="fa-li"><i class="fa fa-location-dot"></i></span>
                            <?= $activity->place ?>
                        </li>
                    </ul>

                </div>
                <a role="button" class="outline secondary" href='/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>'>
                    <i class="fa fa-circle-info"></i>
                    Détails</a>
                <?php if ($can_edit): ?>
                    <a role="button" class="outline secondary"
                        href='/evenements/<?= $event->id ?>/activite/<?= $activity->id ?>/modifier'>
                        <i class="fa fa-pen"></i>
                        Modifier</a>
                <?php endif ?>
                <blockquote>
                    <h6>Inscription</h6>
                    <div class="grid">
                        <ul class="fa-ul">
                            <?php if ($activity_entry?->present): ?>
                                <li><span class="fa-li"><i class="fa fa-check"></i></span><ins>Je participe</ins></li>
                            <?php else: ?>
                                <li><span class="fa-li"><i class="fa fa-xmark"></i></span><del>
                                        <?= $activity_entry ? "Je ne participe pas" : "Pas inscrit" ?>
                                    </del></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($activity_entry?->category): ?>
                            <ul class="fa-ul">
                                <li><span class="fa-li"><abbr title="Catégorie"><i class="fa fa-person-running"></i></abbr></span>
                                    <?= $activity_entry->category?->name ?>
                                </li>
                            </ul>
                        <?php endif ?>
                    </div>
                    <?php if ($activity_entry?->comment): ?>
                        <div>
                            <cite>Remarque : </cite>
                            <?= $activity_entry->comment ?>
                        </div>
                    <?php endif; ?>
                </blockquote>
            </details>
        <?php endforeach; ?>
    <?php endif; ?>


    <?php if ($can_edit): ?>
        <p>
            <a role=button class="secondary" href="/evenements/<?= $event->id ?>/ajouter-activite">
                <i class="fas fa-plus"></i> Ajouter une activité</a>
        </p>
    <?php endif ?>
</article>

<?= UserModal::renderRoot() ?>