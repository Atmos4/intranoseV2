<?php
require_once app_path() . "/components/conditional_icon.php";

function RenderActivityEntry(?Activity $activity, bool $can_register = null)
{
    $activity_entry = $activity->entries[0];
    $extra_info = $activity_entry?->category || $activity_entry?->comment;
    [$open_tag, $close_tag] = $extra_info ? ["<header>", "</header>"] : ["<div class='horizontal'>", "</div>"] ?>
    <article class="notice <?= $activity_entry?->present ? "valid" : "invalid" ?>" <?php if ($can_register): ?>
            data-intro="L'état de votre inscription est disponible ici" <?php endif ?>>
        <?= $open_tag ?>
        <b>
            <?= match (true) {
                !$activity_entry => IconText("fa-question", "Pas encore inscrit", "span"),
                !$activity_entry->present => IconText("fa-xmark", "Je ne participe pas", "del"),
                $activity_entry->present => IconText("fa-check", "Inscrit", "ins"),
                default => "Erreur"
            } ?>
        </b>
        <?php if ($can_register): ?>
            <a href="/evenements/<?= $activity->event->id ?>/inscription_simple" class="outline contrast"
                data-intro="<?= "Vous pouvez " . ($activity_entry ? 'modifier votre inscription' : 'vous inscrire') ?> ici">
                <i class=" fas fa-pen-to-square"></i> <?= $activity_entry ? "Gérer l'inscription" : "S'inscrire" ?>
            </a>
        <?php endif ?>
        <?= $close_tag ?>

        <?php if ($extra_info): ?>
            <div class="row g-2">
                <?php if ($activity_entry?->category): ?>
                    <div class="col-sm-12 col-md">
                        <div class="row g-2">
                            <span title="Catégorie">
                                <?= IconText("fa-person-running", $activity_entry->category?->name) ?>
                            </span>
                        </div>
                    </div>
                <?php endif ?>
                <?php if ($activity_entry?->comment): ?>
                    <div class="col" title="Remarque">
                        <?= IconText("fa-comment", $activity_entry->comment) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif ?>
    </article>
<?php }

function RenderEventEntry(?EventEntry $entry, Event $event, bool $can_edit)
{
    $extra_info = $entry?->present || $entry?->comment;
    [$open_tag, $close_tag] = $extra_info ? ["<header>", "</header>"] : ["<div class='horizontal'>", "</div>"] ?>
    <article class="notice <?= $entry?->present ? "valid" : "invalid" ?>"
        data-intro="L'état de votre inscription est disponible ici">
        <?= $open_tag ?>
        <b>
            <?= match (true) {
                !$entry => IconText("fa-question", "Pas encore inscrit", "span"),
                !$entry->present => IconText("fa-xmark", "Je ne participe pas", "del"),
                $entry->present => IconText("fa-check", "Inscrit", "ins"),
                default => "Erreur"
            } ?>
        </b>
        <?php if (($event->open && $event->deadline >= date_create("today")) || $can_edit): ?>
            <a href="/evenements/<?= $event->id ?>/inscription" class="outline contrast"
                data-intro="<?= "Vous pouvez " . ($entry ? 'modifier votre inscription' : 'vous inscrire') ?> ici">
                <i class="fas fa-pen-to-square"></i> <?= $entry ? "Gérer l'inscription" : "S'inscrire" ?>
            </a>
        <?php endif ?>
        <?= $close_tag ?>
        <?php if ($extra_info): ?>
            <div class="row g-2">
                <?php if ($entry?->present): ?>
                    <div class="col-12 col-md-6">
                        <?= ConditionalIcon($entry->transport, "Transport avec le club") ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= ConditionalIcon($entry->accomodation, "Hébergement avec le club") ?>
                    </div>
                <?php endif ?>
                <?php if ($entry?->comment): ?>
                    <div class="col-12">
                        <i class="fa fa-comment fa-fw"></i>
                        <span class="space-before">
                            <?= $entry->comment ?>
                        </span>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
    </article>
<?php }

function RenderTimeline(Event $event, bool $isPresent)
{
    $today_date = date_create("today");
    $deadline_class = $event->deadline >= $today_date ? "" : ($isPresent ? "completed" : "missed");
    $start_class = $event->start_date < $today_date ? $deadline_class : "";
    $end_class = $event->end_date < $today_date ? $deadline_class : ""; ?>

    <ul class="timeline timeline-vertical lg:timeline-horizontal">
        <li class="<?= $deadline_class ?>">
            <div class="timeline-start">
                Deadline
            </div>
            <div class="timeline-middle">
                <i class="fas fa-clock"></i>
            </div>
            <div class="timeline-end timeline-box">
                <?= format_date($event->deadline, "dd MMM yyyy HH:mm") ?>
            </div>
            <hr>
        </li>
        <li class="<?= $start_class ?>">
            <hr>
            <div class="timeline-start">
                Départ
            </div>
            <div class="timeline-middle lg:rotate">
                <?php include app_path() . "/components/start_icon.php" ?>
            </div>
            <div class="timeline-end timeline-box">
                <?= format_date($event->start_date, "dd MMM yyyy HH:mm") ?>
            </div>
            <hr>
        </li>
        <li class="<?= $end_class ?>">
            <hr>
            <div class="timeline-start">
                Retour
            </div>
            <div class="timeline-middle">
                <?php include app_path() . "/components/finish_icon.php" ?>
            </div>
            <div class="timeline-end timeline-box">
                <?= format_date($event->end_date, "dd MMM yyyy HH:mm") ?>
            </div>
        </li>
    </ul>
<?php }