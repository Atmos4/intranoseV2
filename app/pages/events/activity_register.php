<?php
restrict_access();

include __DIR__ . "/eventUtils.php";

$user = User::getCurrent();

$activity_id = get_route_param("activity_id");
$event_id = get_route_param("event_id");
$activity = em()->find(Activity::class, $activity_id);
$event = em()->find(Event::class, $event_id);

$event_entry = $event->entries[0] ?? null;
$event_present = $event_entry?->present;
$activity_entry = $activity->entries[0] ?? null;
$form_values = [];


if (!$event_entry || !$event_present || $event->deadline < date_create("today")) {
    force_404("this activity is closed for entry");
}

if ($activity_entry) {
    $form_values["activity_entry"] = $activity_entry->present;
    $form_values["activity_comment"] = $activity_entry->comment;
    $form_values["activity_category"] = $activity_entry->category->id ?? "";
}

$v = new Validator($form_values ?? []);
$form_fields["activity_entry"] = $v->switch("activity_entry")->set_labels("Je cours", "Je ne cours pas");
$form_fields["activity_comment"] = $v->textarea("activity_comment")->label("Remarque");
if (count($activity->categories)) {
    $form_fields["form_category"] = $v->select("activity_category")->label("Catégorie")->options(Category::toSelectOptions($activity->categories));
}

if ($v->valid()) {
    // Map activity categories with ids
    $activity_category_map = [];
    foreach ($activity->categories as $category) {
        $activity_category_map[$category->id] = $category;
    }

    $activity_entry = $activity->entries[0] ?? new ActivityEntry();
    $activity_present = $event_present && $form_fields["activity_entry"]->value;
    if ($activity_present) {
        $activity_entry->set(
            $user,
            $activity,
            $activity_present,
            $activity_present ? $form_fields["activity_comment"]->value : "",
        );
        $activity_entry->category = $activity_present ? $activity_category_map[$form_fields["form_category"]->value] : null;
        em()->persist($activity_entry);
    } else {
        em()->remove($activity_entry);
    }
    em()->flush();
    redirect("/evenements/$event_id/activite/$activity_id");
}

page("Inscription - " . $activity->name)->css("event_register.css");
?>
<form id="mainForm" method="post">
    <nav id="page-actions">
        <a href="/evenements/<?= $event->id ?>/activite/<?= $activity_id ?>" class="secondary"><i
                class="fas fa-caret-left"></i> Retour</a>
        <button type="submit" role="button">Enregistrer</button>
    </nav>
    <?= RegisterArticle($v, $event, $form_fields, $activity) ?>
</form>