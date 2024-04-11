<?php
require_once app_path() . "/components/conditional_icon.php";

function IconText($icon, $text, $wrapper = null)
{
    $text = "<i class=\"fas fa-fw $icon\"></i><span class=\"space-before\">$text</span>";
    return $wrapper ? "<$wrapper>$text</$wrapper>" : $text;
}

function ActivityEntry(Activity $activity = null, ?ActivityEntry $activity_entry, bool $can_edit = false)
{ ?>
    <article class="entry-summary <?= $activity_entry?->present ? "entered" : "not-entered" ?>">


        <header class="entry-header">
            <b>
                <?= match (true) {
                    !$activity_entry?->present => IconText($activity_entry ? "fa-xmark" : "fa-question", "Pas inscrit", "span"),
                    $activity_entry->present => IconText("fa-check", "Inscrit", "ins"),
                    default => "Erreur"
                } ?>
            </b>
            <?php if ($activity->date >= date_create("today") || $can_edit): ?>
                <a href="/evenements/<?= $activity->event->id ?>/activite/<?= $activity->id ?>/inscription"
                    class="outline contrast">
                    <i class="fas fa-pen-to-square"></i> <?= $activity_entry ? "Gérer l'inscription" : "S'inscrire" ?>
                </a>
            <?php endif ?>
        </header>
        <div class="row g-2">
            <?php if ($activity_entry?->category): ?>
                <div class="col-12 col-md-6">
                    <span title="Catégorie">
                        <?= IconText("fa-person-running", $activity_entry->category?->name) ?>
                    </span>
                </div>
            <?php endif ?>

            <?php if ($activity_entry?->comment): ?>
                <div class="col-sm-12 col-md-6" title="Remarque">
                    <?= IconText("fa-comment", $activity_entry->comment) ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
<?php }