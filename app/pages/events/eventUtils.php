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
                <a href="/evenements/<?= $activity->event->id ?>/inscription" class="outline contrast">
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

//TODO: make the entry optional, and adapt the logic
function RegisterArticle(Validator $v, Event $event, array $form_fields, Activity $activity = null)
{

    function getToggleClass($selector, $initialState)
    {
        return $selector . ($initialState ? "" : " hidden");
    }

    $main_entry_toggle = $activity ? $form_fields["activity_entry"] : $form_fields["event_present"];

    $main_toggle_class = getToggleClass("toggleDiv", $main_entry_toggle->value);
    ?>

    <article>
        <header class="center">
            <?= $v->render_validation() ?>
            <?php BasicInfos($event, $activity) ?>
            <fieldset>
                <b>
                    <?= $main_entry_toggle->attributes(["onchange" => "toggleDisplay(this,'.toggleDiv')"])->render() ?>
                </b>
            </fieldset>
        </header>

        <div class="<?= $main_toggle_class ?>">
            <?php if ($activity): ?>

                <fieldset class="row">
                    <?php if (count($activity->categories)): ?>
                        <div class="col-sm-12 col-md-6">
                            <?= $form_fields["form_category"]->render() ?>
                        </div>
                    <?php endif ?>
                </fieldset>
                <fieldset>
                    <?= $form_fields["activity_comment"]->render() ?>
                </fieldset>

            <?php else: ?>

                <fieldset class="row">
                    <div class="col-sm-6">
                        <?= $form_fields["transport"]->render() ?>
                    </div>
                    <div class="col-sm-6">
                        <?= $form_fields["accomodation"]->render() ?>
                    </div>
                </fieldset>
                <fieldset>
                    <?= $form_fields["event_comment"]->render() ?>
                </fieldset>

                <?php if (count($event->activities)): ?>
                    <h4>Courses : </h4>
                    <table role="grid">
                        <?php foreach ($event->activities as $index => $activity_item):
                            $activity_form = $form_fields["activity_rows"][$index];
                            $toggle_class = getToggleClass("activityToggle$index", $activity_form['entry']->value); ?>
                            <tr class="display">
                                <td class="activity-name"><b>
                                        <?= $activity_item->name ?>
                                    </b></td>
                                <td class="activity-date">
                                    <?= format_date($activity_item->date) ?>
                                </td>
                                <td class="activity-place">
                                    <?php if ($activity_item->location_url): ?>
                                        <a href=<?= $activity_item->location_url ?> target=”_blank”><?= $activity_item->location_label ?></a>
                                    <?php else: ?>
                                        <?= $activity_item->location_label ?>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <tr class="edit">
                                <td colspan="3">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div>
                                                <?= $activity_form["entry"]->attributes(["onchange" => "toggleDisplay(this,'.activityToggle$index')"])->render() ?>
                                            </div>
                                        </div>
                                        <?php if (count($activity_item->categories)): ?>
                                            <div class="col-sm-12 col-md-6 <?= $toggle_class ?>">
                                                <?= $activity_form["category"]->render() ?>
                                            </div>
                                        <?php endif ?>
                                        <div class="col-12 <?= $toggle_class ?>">
                                            <?= $activity_form["comment"]->render() ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                <?php endif ?>
            <?php endif ?>
        </div>
        <?php if (!$activity): ?>
            <div id="conditionalText">
                <p>Inscris-toi pour une vraie partie de plaisir !</p>
                <fieldset>
                    <?= $form_fields["event_comment_noentry"]->render() ?>
                </fieldset>
            </div>
        <?php endif ?>
    </article>

    <script>
        function toggleDisplay(toggle, target) {
            const elements = document.querySelectorAll(target);
            for (element of elements) {
                if (toggle.checked) {
                    element.classList.remove("hidden");
                } else {
                    element.classList.add("hidden");
                }
            }

        }
    </script>

<?php }

function BasicInfos(Event $event, Activity $activity = null)
{
    ?>
    <div class="row">
        <div class="col-sm-6">
            <?php include app_path() . "/components/start_icon.php" ?>
            <span>
                <?php if ($activity): ?>
                    <?= "Départ - " . format_date($activity->date) ?>
                <?php else: ?>
                    <?= "Départ - " . format_date($event->start_date) ?>
                <?php endif ?>
            </span>
        </div>
        <?php if (!$activity): ?>
            <div class="col-sm-6">
                <?php include app_path() . "/components/finish_icon.php" ?>
                <span>
                    <?= "Retour - " . format_date($event->end_date) ?>
                </span>
            </div>
        <?php endif ?>
        <div>
            <i class="fas fa-clock"></i>
            <span>
                <?= "Date limite - " . format_date($event->deadline) ?>
            </span>
        </div>
        <?php if ($activity): ?>
            <span>
                <i class="fa fa-location-dot fa-fw"></i>
                <?php if ($activity->location_url): ?>
                    <a href=<?= $activity->location_url ?> target=”_blank”><?= $activity->location_label ?></a>
                <?php else: ?>
                    <?= $activity->location_label ?>
                <?php endif ?>
            </span>
        <?php endif ?>
    </div>
    <?php
}