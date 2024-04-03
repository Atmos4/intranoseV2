<?php
restrict_dev();

$races = em()->createQuery("SELECT r FROM Race r JOIN Event e JOIN RaceEntry re JOIN Category c")->getResult();
$activities = em()->getRepository(Activity::class)->findAll();

$v_up = new Validator(action: "migrate_activities_up");

$v_down = new Validator(action: "migrate_activities_down");

$logger->debug("races", $races);
$logger->debug("activities", $activities);

if ($v_up->valid()) {

    #migrate race
    foreach ($races as $race) {
        $newActivity = new Activity();
        $newActivity->name = $race->name;
        $newActivity->date = $race->date;
        $newActivity->location_label = $race->place;
        $newActivity->location_url = '';
        $newActivity->description = '';
        $newActivity->type = ActivityType::RACE;
        $newActivity->event = $race->event;
        em()->persist($newActivity);

        #create activity entries corresponding to the race entries, and connect them to the new activity
        foreach ($race->entries as $entry) {
            $newEntry = new ActivityEntry();
            $newEntry->user = $entry->user;
            $newEntry->activity = $newActivity;
            $newEntry->category = $entry->category;
            $newEntry->present = $entry->present;
            $newEntry->comment = $entry->comment;
            em()->persist($newEntry);
        }
    }
    em()->flush();
}

if ($v_down->valid()) {
    foreach ($activities as $activity) {
        em()->remove($activity);
    }
    em()->flush();
}

page("Migration")->disableNav();
?>
<?php if ($v_up->valid()):
    $count = count($races);
    echo "Migration de $count courses vers les activités ✅";
elseif ($v_down->valid()):
    $count = count($activities);
    echo "Suppression de $count activités 💥";
else: ?>
    <form method="post" id="migrationUp">
        <?= $v_up->render_validation() ?>
        <h3>Up</h3>
        <p>Sûr de vouloir migrer ?
        </p>
        <button>Let's go 🚀</button>
    </form>
    <form method="post" id="migrationDown">
        <?= $v_down->render_validation() ?>
        <h3>Down</h3>
        <p>Sûr de vouloir revenir en arrière ?
        </p>
        <button>Let's go 🚀</button>
    </form>
<?php endif ?>