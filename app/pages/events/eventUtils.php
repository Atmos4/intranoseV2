<?php
require_once app_path() . "/components/conditional_icon.php";

function IconText($icon, $text, $wrapper = null)
{
    $text = "<i class=\"fas fa-fw $icon\"></i><span class=\"space-before\">$text</span>";
    return $wrapper ? "<$wrapper>$text</$wrapper>" : $text;
}

function RenderActivityEntry(?Activity $activity, bool $can_register = null)
{
    $activity_entry = $activity->entries[0];
    $extra_info = $activity_entry?->category || $activity_entry?->comment;
    [$open_tag, $close_tag] = $extra_info ? ["<header>", "</header>"] : ["<div class='horizontal'>", "</div>"] ?>
    <article class="notice <?= $activity_entry?->present ? "valid" : "invalid" ?>">
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
            <a href="/evenements/<?= $activity->event->id ?>/inscription_simple" class="outline contrast">
                <i class="fas fa-pen-to-square"></i> <?= $activity_entry ? "Gérer l'inscription" : "S'inscrire" ?>
            </a>
        <?php endif ?>
        <?= $close_tag ?>

        <?php if ($extra_info): ?>
            <div class="row g-2">
                <div class="col-sm-12 col-md-6">
                    <div class="row g-2">
                        <?php if ($activity_entry?->category): ?>
                            <span title="Catégorie">
                                <?= IconText("fa-person-running", $activity_entry->category?->name) ?>
                            </span>
                        <?php endif ?>
                    </div>
                </div>
                <?php if ($activity_entry?->comment): ?>
                    <div class="col-sm-12 col-md-6" title="Remarque">
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
    <article class="notice <?= $entry?->present ? "valid" : "invalid" ?>">
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
            <a href="/evenements/<?= $event->id ?>/inscription" class="outline contrast">
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