<?php
restrict_access();
$can_edit = check_auth(Access::$ADD_EVENTS);
$event_id = get_route_param('event_id');
$pool_id = get_route_param('pool_id', false);
$event = em()->find(Event::class, $event_id);
$all_event_entries = EventService::getAllEntries($event_id);

if (!$pool_id) {
    redirect("/evenements/$event_id?tab=pools");
}

$team_group = em()->find(TeamGroup::class, $pool_id);
if (!$team_group || $team_group->event->id !== $event->id) {
    Toast::error("Pool d'équipes introuvable");
    redirect("/evenements/$event_id?tab=pools");
}

if (!$team_group->published && !$can_edit) {
    Toast::error("Pool d'équipes introuvable");
    redirect("/evenements/$event_id?tab=pools");
}

$existing_teams = $team_group->teams->toArray();

// If activity linked, use activity entries; otherwise use event entries
$linked_activity = $team_group->activity;
$registered_users = [];
$user_categories = []; // user_id => computed category (e.g. "H21", "D16")

if ($linked_activity) {
    $activity_entries = ActivityService::getActivityEntries($linked_activity->id);
    foreach ($activity_entries as $entry) {
        if ($entry->present) {
            $registered_users[] = $entry->user;
        }
    }
} else {
    foreach ($all_event_entries as $event_entry) {
        if ($event_entry->present) {
            $registered_users[] = $event_entry->user;
        }
    }
}

// Compute categories from user birthdate + gender
foreach ($registered_users as $user) {
    $user_categories[$user->id] = RelayFormatService::computeCategory($user, $event->start_date);
}
// Also compute for team members not in the registered list
foreach ($existing_teams as $team) {
    foreach ($team->members as $member) {
        if (!isset($user_categories[$member->id])) {
            $user_categories[$member->id] = RelayFormatService::computeCategory($member, $event->start_date);
        }
    }
}

// User IDs already assigned to a team
$assigned_user_ids = [];
foreach ($existing_teams as $team) {
    foreach ($team->members as $member) {
        $assigned_user_ids[] = $member->id;
    }
}

$v = new Validator();

if ($can_edit && $v->valid()) {
    $team_count = intval($_POST["team_count"] ?? 0);
    $submitted_team_ids = [];
    $saved_teams = [];

    for ($i = 0; $i < $team_count; $i++) {
        // Skip removed teams
        if (!isset($_POST["team_{$i}_name"]))
            continue;

        $team_db_id = $_POST["team_{$i}_id"] ?? null;
        $team_name = $_POST["team_{$i}_name"] ?? "Équipe " . ($i + 1);
        $member_ids = $_POST["team_{$i}_members"] ?? [];
        $team_relay_format = $_POST["team_{$i}_relay_format"] ?? null;

        if ($team_db_id) {
            $team = em()->find(Team::class, $team_db_id);
            $submitted_team_ids[] = intval($team_db_id);
        } else {
            $team = new Team();
            $team->team_group = $team_group;
            $team->members = new \Doctrine\Common\Collections\ArrayCollection();
        }

        $team->name = $team_name;
        $team->relay_format = $team_relay_format ?: null;

        // Persist slot/member order (array of user IDs, with empty strings for empty slots)
        $team->slot_order = json_encode(array_values($member_ids));

        $team->members->clear();
        foreach ($member_ids as $member_id) {
            if ($member_id) {
                $user = em()->find(User::class, intval($member_id));
                if ($user) {
                    $team->members->add($user);
                }
            }
        }

        em()->persist($team);
        $saved_teams[] = $team;
    }


    foreach ($existing_teams as $existing_team) {
        if (!in_array($existing_team->id, $submitted_team_ids)) {
            em()->remove($existing_team);
        }
    }

    em()->flush();

    Toast::success("Équipes sauvegardées");
    #redirect("/evenements/$event_id/pool/$pool_id");
}

page(($team_group->name ?: "Pool #$pool_id") . " - " . $event->name)->css("team_builder.css")->script("team_builder.js");
?>

<?php $actions = actions()->back("/evenements/$event_id?tab=pools");
if ($can_edit) {
    $actions->dropdown(function ($d) use ($event_id, $pool_id, $team_group) {
        $d->link("/evenements/$event_id/pool/$pool_id/modifier", "Modifier", "fa-pen", ["class" => "secondary"]);
        $d->link("/evenements/$event_id/pool/$pool_id/supprimer", "Supprimer", "fa-trash", ["class" => "destructive"]);
        $team_group->published
            ? $d->link("/evenements/$event_id/pool/$pool_id/publier", "Retirer", "fa-eye-slash", ["class" => "destructive"])
            : $d->link("/evenements/$event_id/pool/$pool_id/publier", "Publier", "fa-eye", ["class" => "secondary"]);
    });
}
echo $actions; ?>

<form method="post" id="teams-form">
    <?php if ($can_edit): ?>
        <?= $v->render_validation() ?>
    <?php endif ?>

    <h3>
        <i class="fa fa-people-group"></i>
        <?= $team_group->name ?>
    </h3>

    <?php $relay_group = $team_group->getRelayGroup(); ?>
    <?php if ($relay_group): ?>
        <p class="relay-group-badge">
            <i class="fa fa-trophy"></i>
            <?= htmlspecialchars($relay_group->name) ?>
        </p>
    <?php endif ?>

    <?php if ($can_edit): ?>
        <h4>Participants <?= $linked_activity ? "inscrits à " . htmlspecialchars($linked_activity->name) : "inscrits" ?>
        </h4>
        <p class="teams-subtitle">Glissez-déposez les participants dans les équipes</p>
        <div class="users-scroll-container" id="users-container">
            <?php foreach ($registered_users as $user):
                $category = $user_categories[$user->id] ?? null;
                ?>
                <div class="user-drag-item <?= in_array($user->id, $assigned_user_ids) ? 'user-in-team' : '' ?>"
                    draggable="true" data-user-id="<?= $user->id ?>"
                    data-user-name="<?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>"
                    data-user-picture="<?= htmlspecialchars($user->getPicture()) ?>"
                    data-user-category="<?= htmlspecialchars($category ?? '') ?>">
                    <img src="<?= $user->getPicture() ?>" alt="">
                    <span><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></span>
                    <?php if ($category): ?>
                        <small class="user-category-badge"><?= htmlspecialchars($category) ?></small>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <h4>Équipes</h4>
    <div class="teams-scroll-container" id="teams-container" data-event-id="<?= $event_id ?>"
        data-pool-id="<?= $pool_id ?>" data-team-count="<?= count($existing_teams) ?>"
        data-can-edit="<?= $can_edit ? 'true' : 'false' ?>">
        <?php if (count($existing_teams)):
            foreach ($existing_teams as $index => $team): ?>
                <div id="team-wrapper-<?= $index ?>" hx-post="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_form"
                    hx-trigger="load" hx-swap="outerHTML" hx-vals='<?= htmlspecialchars(json_encode([
                        "action" => $index,
                        "form_values" => [
                            "team_id" => $team->id,
                            "team_name" => $team->name ?: "Équipe " . ($index + 1),
                            "team_relay_format" => $team->relay_format,
                            "team_members" => array_map(fn($m) => $m ? [
                                "id" => $m->id,
                                "name" => $m->first_name . ' ' . $m->last_name,
                                "picture" => $m->getPicture(),
                                "category" => $user_categories[$m->id] ?? null,
                            ] : null, $team->getOrderedMembers()),
                            "can_edit" => $can_edit,
                        ]
                    ]), ENT_QUOTES, 'UTF-8') ?>'>
                </div>
            <?php endforeach;
        endif ?>

        <?php if ($can_edit): ?>
            <div class="team-add-column" onclick="addTeam()">
                <i class="fa fa-plus"></i>
                <span>Ajouter une équipe</span>
            </div>
        <?php endif ?>
    </div>

    <?php if ($can_edit): ?>
        <input type="hidden" id="team-count" name="team_count" value="<?= count($existing_teams) ?>">

        <div style="margin-top: 1rem; text-align: right;">
            <button type="submit">
                <i class="fa fa-save"></i> Sauvegarder
            </button>
        </div>
    <?php endif ?>
</form>