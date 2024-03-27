<?php
restrict_dev();

$races = em()->createQuery("SELECT r, e.id FROM Race r JOIN Event e")->getArrayResult();

$race_entries = em()->createQuery("SELECT re, c.id FROM RaceEntry re LEFT JOIN re.category c")->getArrayResult();

var_dump($races);
var_dump($race_entries);

$v = new Validator(action: "migrate_races");

if ($v->valid()) {

    #migrate race
    foreach ($races as $race) {
        $newActivity = new Activity();
        $newActivity->name = $race[0]['name'];
        $newActivity->date = $race[0]['date'];
        $newActivity->location_label = $race[0]['place'];
        $newActivity->location_url = '';
        $newActivity->description = '';
        $newActivity->type = ActivityType::RACE;
        $newActivity->event = em()->getReference(Event::class, $race['id']);
        em()->persist($newActivity);
    }
    #first flush so that we can use the new activities when creating entries
    em()->flush();

    #migrate race entries
    foreach ($race_entries as $entry) {
        $newEntry = new ActivityEntry();
        $newEntry->user = em()->getReference(User::class, $entry[0]['user_id']);
        # make the link between the race and the corresponding activity
        $race = em()->createQuery("SELECT r FROM Race r WHERE r.id = :raceId")->setParameter('raceId', $entry[0]['race_id'])->getArrayResult()[0];
        # match by name and date
        $activity_id = em()->createQuery("SELECT a.id FROM Activity a WHERE a.name = :name AND a.date = :date")
            ->setParameters([
                'name' => $race["name"],
                'date' => $race["date"]
            ])->getSingleScalarResult();
        var_dump($entry['id']);
        $newEntry->activity = em()->getReference(Activity::class, $activity_id);
        $newEntry->category = $entry['id'] ? em()->getReference(Category::class, $entry['id']) : null;
        $newEntry->present = true;
        $newEntry->comment = '';
        em()->persist($newEntry);
    }
    em()->flush();
}

page("Migration")->disableNav();
?>
<?php if ($v->valid()):
    echo "Migration des courses vers les activités ✅";
else: ?>
    <form method="post">
        <?= $v->render_validation() ?>
        <p>Sûr de vouloir migrer ?
        </p>
        <button>Let's go 🚀</button>
    </form>
<?php endif ?>