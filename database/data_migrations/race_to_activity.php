<?php
restrict_environment("ENABLE_RACE_MIGRATION");

include __DIR__ . "/dataMigrationUtils.php";

$races = getRaces();
$activities = getActivities();
$raceCount = count($races);
$activityCount = count($activities);

$v_up = new Validator(action: "migrate_activities_up");
$v_down = new Validator(action: "migrate_activities_down");

logger()->debug("races", ["races" => $races]);
logger()->debug("activities", ["activities" => $activities]);

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

        foreach ($race->categories as $c) {
            $c->activity = $newActivity;
        }
    }
    em()->flush();
    Toast::success("Migration de $raceCount courses vers les activités ✅");
    redirect("/dev/migrate_activities");
}

if ($v_down->valid()) {
    foreach ($activities as $activity) {
        foreach ($activity->categories as $c) {
            if ($c->race) {
                $activity->categories->removeElement($c);
                $c->activity = null;
            }
        }
        em()->remove($activity);
    }
    em()->flush();
    Toast::success("Suppression de $activityCount activités 💥");
    redirect("/dev/migrate_activities");
}

$status = match (true) {
    ($activityCount === 0) => 'ready to migrate',
    ($activityCount === $raceCount) => 'migration successful',
    ($activityCount > $raceCount) => "dangerous!! More activities than races",
    default => "unknown",
};

page("Races to activities");
?>
<div>
    <p>Status:
        <?= $status ?>
    </p>
    <ul>
        <li>Races:
            <?= $raceCount ?>
        </li>
        <li>Activities:
            <?= $activityCount ?>
        </li>
    </ul>
    <h3>Actions:</h3>
    <div class="row">
        <form method="post" class="col-auto">
            <?= $v_up->render_validation() ?>
            <button>Up ⬆️</button>
        </form>
        <form method="post" class="col-auto">
            <?= $v_down->render_validation() ?>
            <button class=destructive>Down ⬇️</button>
        </form>
    </div>
</div>