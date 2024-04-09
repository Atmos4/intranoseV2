<?php
require_once app_path() . "/components/conditional_icon.php";

function IconText($icon, $text, $wrapper = null)
{
    $text = "<i class=\"fas fa-fw $icon\"></i><span class=\"space-before\">$text</span>";
    return $wrapper ? "<$wrapper>$text</$wrapper>" : $text;
}

function ActivityEntry(?ActivityEntry $activity_entry)
{ ?>
    <article class="entry-summary <?= $activity_entry?->present ? "entered" : "not-entered" ?>">
        <div class="row g-2">
            <div class="col-sm-12 col-md-6">
                <div class="row g-2">
                    <?= match (true) {
                        !$activity_entry => IconText("fa-question", "Pas encore inscrit", "span"),
                        !$activity_entry->present => IconText("fa-xmark", "Je ne participe pas", "del"),
                        $activity_entry->present => IconText("fa-check", "Inscrit", "ins"),
                        default => "Erreur"
                    } ?>
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
    </article>
<?php }