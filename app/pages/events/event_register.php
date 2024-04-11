<?php
restrict_access();

include __DIR__ . "/eventUtils.php";

$user = User::getCurrent();
$event = Event::getWithGraphData(get_route_param('event_id'), $user->id);

if (!check_auth(Access::$ADD_EVENTS) && (!$event->open || $event->deadline < date_create("today"))) {
    force_404("this event is closed for entry");
}

$event_entry = $event->entries[0] ?? null;
$form_values = [];

if ($event_entry) {
    $form_values = [
        "event_present" => $event_entry->present,
        "event_transport" => $event_entry->transport,
        "event_accomodation" => $event_entry->accomodation,
        "event_comment" => $event_entry->comment,
        "event_comment_noentry" => $event_entry->comment,
    ];
}

foreach ($event->activities as $index => $activity) {
    $activity_entry = $activity->entries[0] ?? null;
    if ($activity_entry) {
        $form_values["activity_{$index}_entry"] = $activity_entry->present;
        $form_values["activity_{$index}_comment"] = $activity_entry->comment;
        $form_values["activity_{$index}_category"] = $activity_entry->category->id ?? "";
    }
}


$v = new Validator($form_values ?? []);
$form_fields = [];
$form_fields["event_present"] = $v->switch("event_present")->set_labels("Je participe", "Pas inscrit");
$form_fields["transport"] = $v->switch("event_transport")->label("Transport");
$form_fields["accomodation"] = $v->switch("event_accomodation")->label("Hébergement");
$form_fields["event_comment"] = $v->textarea("event_comment")->label("Remarques");
$form_fields["event_comment_noentry"] = $v->textarea("event_comment_noentry")->label("Remarque");
$form_fields["activity_rows"] = [];
foreach ($event->activities as $index => $activity) {
    $form_fields["activity_rows"][$index]["entry"] = $v->switch("activity_{$index}_entry")->set_labels("Je cours", "Je ne cours pas");
    $form_fields["activity_rows"][$index]["comment"] = $v->textarea("activity_{$index}_comment")->label("Remarque");
    if (count($activity->categories)) {
        $form_fields["activity_rows"][$index]["category"] = $v->select("activity_{$index}_category")->label("Catégorie")
            ->options(Category::toSelectOptions($activity->categories));
    }
}

if ($v->valid()) {
    $event_entry ??= new EventEntry();
    $event_entry->set(
        $user,
        $event,
        $form_fields["event_present"]->value,
        $form_fields["event_present"]->value && $form_fields["transport"]->value,
        $form_fields["event_present"]->value && $form_fields["accomodation"]->value,
        date_create(),
        $form_fields["event_present"]->value ? $form_fields["event_comment"]->value : $form_fields["event_comment_noentry"]->value,
    );
    em()->persist($event_entry);

    foreach ($event->activities as $index => $activity) {
        // Map activity categories with ids
        $activity_category_map = [];
        foreach ($activity->categories as $category) {
            $activity_category_map[$category->id] = $category;
        }

        $activity_entry = $activity->entries[0] ?? new ActivityEntry();
        $activity_form = $form_fields["activity_rows"][$index];
        $activity_present = $form_fields["event_present"]->value && $activity_form["entry"]->value;
        if ($activity_present) {
            $activity_entry->set(
                $user,
                $activity,
                $activity_present,
                $activity_present ? $activity_form["comment"]->value : "",
            );
            $activity_entry->category = $activity_present ? $activity_category_map[$activity_form["category"]->value] : null;
            em()->persist($activity_entry);
        } else {
            em()->remove($activity_entry);
        }
    }
    em()->flush();
    redirect("/evenements/$event->id");
}

page("Inscription - " . $event->name)->css("event_register.css");
?>
<form id="mainForm" method="post">
    <nav id="page-actions">
        <a href="/evenements/<?= $event->id ?>" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
        <button type="submit" role="button">Enregistrer</button>
    </nav>
    <?= RegisterArticle($v, $event, $form_fields) ?>
</form>