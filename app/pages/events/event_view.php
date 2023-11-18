<?php
restrict_access();

require_once app_path() . "/components/conditional_icon.php";

$event = Event::getWithGraphData(get_route_param('event_id'), User::getCurrent()->id);
if (!$event->open) {
    restrict_access(Access::$ADD_EVENTS);
}

$all_event_entries = Event::getAllEntries($event->id);

$can_edit = check_auth(Access::$ADD_EVENTS);


$entry = $event->entries->get(0) ?? null;

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
                    <?php if ($event->open): ?>
                        <li>
                            <a href="/evenements/<?= $event->id ?>/participants" class="secondary">
                                <i class="fas fa-users"></i> Afficher les participants
                            </a>
                        </li>
                    <?php endif; ?>
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
            <b>
                <?php if ($event->open): ?>
                    <?php if ($entry?->present): ?>
                        <ins><i class="fas fa-check"></i>
                            <span>Je participe</span></ins>
                    <?php else: ?>
                        <del><i class="fas fa-xmark"></i>
                            <span>
                                <?= $entry ? "Je ne participe pas" : "Pas inscrit" ?>
                            </span></del>
                    <?php endif; ?>
                <?php else: ?>
                    <del>Pas encore publié</del>
                <?php endif ?>
            </b>
        </div>

        <?php if ($event->bulletin_url): ?>
            <a href="<?= $event->bulletin_url ?>" target="_blank"> <i class="fa fa-paperclip"></i> Bulletin
                <i class="fa fa-external-link"></i></a>
        <?php endif ?>
    </header>

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
        <blockquote>
            <?= $entry->comment ?>
        </blockquote>
    <?php endif; ?>

    <?php if (count($event->races)): ?>
        <h4>Courses : </h4>
        <table role="grid">

            <?php foreach ($event->races as $race):
                $race_entry = $race->entries[0] ?? null; ?>
                <details>
                    <summary>
                        <?= ConditionalIcon($race_entry && $race_entry->present) . " " . $race->name ?>
                    </summary>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <ul class="fa-ul">
                                <li><span class="fa-li"><i class="fa fa-calendar"></i></span>
                                    <?= format_date($race->date) ?>
                                </li>
                                <li><span class="fa-li"><i class="fa fa-location-dot"></i></span>
                                    <?= $race->place ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <ul class="fa-ul">
                                <?php if ($race_entry?->present): ?>
                                    <li><span class="fa-li"><i class="fa fa-check"></i></span><ins>Je participe</ins></li>
                                <?php else: ?>
                                    <li><span class="fa-li"><i class="fa fa-xmark"></i></span><del>
                                            <?= $race_entry ? "Je ne participe pas" : "Pas inscrit" ?>
                                        </del></li>
                                <?php endif; ?>
                                <?php if ($race_entry?->category): ?>
                                    <li><span class="fa-li"><i class="fa fa-person-running"></i></span>
                                        <?= $race_entry->category?->name ?>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                    </div>
                    <?php if ($race_entry?->comment): ?>
                        <div>
                            <cite>Remarque : </cite>
                            <?= $race_entry->comment ?>
                        </div>
                        <br>
                    <?php endif;
                    if ($can_edit): ?>
                        <nav>
                            <li></li>
                            <li>
                                <?php if ($event->open): ?>
                                    <a role="button" class="outline secondary" href='/evenements/course/<?= $race->id ?>/inscrits'> <i
                                            class="fa fa-users"></i>
                                        Inscrits</a>
                                <?php endif ?>
                                <a role="button" class="outline secondary"
                                    href='/evenements/<?= $event->id ?>/course/<?= $race->id ?>'>
                                    <i class="fa fa-pen"></i>
                                    Modifier</a>
                            </li>
                        </nav>

                    <?php endif ?>
                </details>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>


    <?php if ($can_edit): ?>
        <p>
            <a role=button class="secondary" href="/evenements/<?= $event->id ?>/ajouter-course">
                <i class="fas fa-plus"></i> Ajouter une course</a>
        </p>
    <?php endif; ?>

    <?php if ($event->open): ?>
        <footer>
            <h4>Participants : </h4>
            <table>
                <tbody>
                    <?php foreach ($all_event_entries as $entry): ?>
                        <?php if ($entry->present): ?>
                            <tr>
                                <td>
                                    <a href="/licencies?user<?= $entry->user->id ?>" <?= UserModal::props($entry->user->id) ?>>
                                        <?= $entry->user->last_name . " " . $entry->user->first_name ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach ?>
                </tbody>
            </table>
        </footer>
    <?php endif ?>
</article>

<?= UserModal::renderRoot() ?>