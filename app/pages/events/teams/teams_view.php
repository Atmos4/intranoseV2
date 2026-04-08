<?php
restrict_access(Access::$ADD_EVENTS);
require __DIR__ . "/../../../components/user_card.php";
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

$existing_teams = $team_group->teams->toArray();

$registered_users = [];
foreach ($all_event_entries as $event_entry) {
    if ($event_entry->present) {
        $registered_users[] = $event_entry->user;
    }
}

// User IDs already assigned to a team
$assigned_user_ids = [];
foreach ($existing_teams as $team) {
    foreach ($team->members as $member) {
        $assigned_user_ids[] = $member->id;
    }
}

$form_values = [
    "pool_name" => $team_group->name
];

$v = new Validator($form_values);
$pool_name = $v->text("pool_name")->required()->placeholder("Nom du Pool")->attributes(["class" => "pool-name-input"]);

if ($v->valid()) {
    $team_count = intval($_POST["team_count"] ?? 0);
    var_dump($team_count);
    $submitted_team_ids = [];

    for ($i = 0; $i < $team_count; $i++) {
        // Skip removed teams
        if (!isset($_POST["team_{$i}_name"]))
            continue;

        $team_db_id = $_POST["team_{$i}_id"] ?? null;
        $team_name = $_POST["team_{$i}_name"] ?? "Équipe " . ($i + 1);
        $member_ids = $_POST["team_{$i}_members"] ?? [];

        if ($team_db_id) {
            $team = em()->find(Team::class, $team_db_id);
            $submitted_team_ids[] = intval($team_db_id);
        } else {
            $team = new Team();
            $team->team_group = $team_group;
            $team->members = new \Doctrine\Common\Collections\ArrayCollection();
        }

        $team->name = $team_name;

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
    }


    foreach ($existing_teams as $existing_team) {
        if (!in_array($existing_team->id, $submitted_team_ids)) {
            em()->remove($existing_team);
        }
    }

    // Update pool name
    $team_group->name = $pool_name->value ?? $team_group->name;
    em()->persist($team_group);

    em()->flush();

    Toast::success("Équipes sauvegardées");
    redirect("/evenements/$event_id/pool/$pool_id");
}

page(($team_group->name ?: "Pool #$pool_id") . " - " . $event->name)->css("team_builder.css");
?>

<?= actions()->back("/evenements/$event_id?tab=pools") ?>

<form method="post" id="teams-form">
    <?= $v->render_validation() ?>

    <h3>
        <i class="fa fa-users-gear"></i>
        <?= $pool_name->render() ?>
    </h3>

    <h4>Participants inscrits</h4>
    <p class="teams-subtitle">Glissez-déposez les participants dans les équipes</p>
    <div class="users-scroll-container" id="users-container">
        <?php foreach ($registered_users as $user): ?>
            <div class="user-drag-item <?= in_array($user->id, $assigned_user_ids) ? 'user-in-team' : '' ?>"
                draggable="true" data-user-id="<?= $user->id ?>"
                data-user-name="<?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>"
                data-user-picture="<?= htmlspecialchars($user->getPicture()) ?>">
                <img src="<?= $user->getPicture() ?>" alt="">
                <span><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></span>
            </div>
        <?php endforeach ?>
    </div>


    <h4>Équipes</h4>
    <div class="teams-scroll-container" id="teams-container">
        <?php if (count($existing_teams)):
            foreach ($existing_teams as $index => $team): ?>
                <div id="team-wrapper-<?= $index ?>" hx-post="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_form"
                    hx-trigger="load" hx-swap="outerHTML" hx-vals='<?= htmlspecialchars(json_encode([
                        "action" => $index,
                        "form_values" => [
                            "team_id" => $team->id,
                            "team_name" => $team->name ?: "Équipe " . ($index + 1),
                            "team_members" => array_map(fn($m) => [
                                "id" => $m->id,
                                "name" => $m->first_name . ' ' . $m->last_name,
                                "picture" => $m->getPicture(),
                            ], $team->members->toArray()),
                        ]
                    ]), ENT_QUOTES, 'UTF-8') ?>'>
                </div>
            <?php endforeach;
        endif ?>

        <div class="team-add-column" onclick="addTeam()">
            <i class="fa fa-plus"></i>
            <span>Ajouter une équipe</span>
        </div>
    </div>

    <input type="hidden" id="team-count" name="team_count" value="<?= count($existing_teams) ?>">

    <div style="margin-top: 1rem; text-align: right;">
        <button type="submit">
            <i class="fa fa-save"></i> Sauvegarder
        </button>
    </div>
</form>

<script>
    (function () {
        var teamCount = <?= count($existing_teams) ?>;

        // --- Drag & Drop ---
        document.getElementById('users-container').addEventListener('dragstart', function (e) {
            var item = e.target.closest('.user-drag-item');
            if (!item) return;
            e.dataTransfer.setData('text/plain', JSON.stringify({
                id: item.dataset.userId,
                name: item.dataset.userName,
                picture: item.dataset.userPicture
            }));
            e.dataTransfer.effectAllowed = 'move';
        });

        document.getElementById('teams-container').addEventListener('dragover', function (e) {
            if (e.target.closest('.team-drop-zone')) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                e.target.closest('.team-drop-zone').classList.add('drag-over');
            }
        });

        document.getElementById('teams-container').addEventListener('dragleave', function (e) {
            var zone = e.target.closest('.team-drop-zone');
            if (zone && !zone.contains(e.relatedTarget)) {
                zone.classList.remove('drag-over');
            }
        });

        document.getElementById('teams-container').addEventListener('drop', function (e) {
            var zone = e.target.closest('.team-drop-zone');
            if (!zone) return;
            e.preventDefault();
            zone.classList.remove('drag-over');

            var data = JSON.parse(e.dataTransfer.getData('text/plain'));
            var userId = data.id;
            var teamIndex = zone.dataset.teamIndex;

            // One user per team only: remove from any other team first
            document.querySelectorAll('.team-member-chip[data-user-id="' + userId + '"]').forEach(function (el) {
                el.remove();
            });
            document.querySelectorAll('input[data-member-user-id="' + userId + '"]').forEach(function (el) {
                el.remove();
            });

            // Check not already in this team
            if (zone.querySelector('.team-member-chip[data-user-id="' + userId + '"]')) return;

            // Add member chip
            var chip = document.createElement('div');
            chip.className = 'team-member-chip';
            chip.dataset.userId = userId;
            chip.innerHTML = '<img src="' + data.picture + '" alt="">'
                + '<span>' + data.name + '</span>'
                + '<button type="button" onclick="removeMember(this, ' + userId + ')">&times;</button>';
            zone.appendChild(chip);

            // Add hidden input
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'team_' + teamIndex + '_members[]';
            input.value = userId;
            input.dataset.memberUserId = userId;
            zone.closest('.team-column').appendChild(input);

            // Mark user as assigned
            var userItem = document.querySelector('.user-drag-item[data-user-id="' + userId + '"]');
            if (userItem) userItem.classList.add('user-in-team');
        });

        // --- Team management ---
        window.addTeam = function () {
            var wrapper = document.createElement('div');
            wrapper.id = 'team-wrapper-' + teamCount;
            wrapper.setAttribute('hx-post', '/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_form');
            wrapper.setAttribute('hx-trigger', 'load');
            wrapper.setAttribute('hx-swap', 'outerHTML');
            wrapper.setAttribute('hx-vals', JSON.stringify({
                action: teamCount,
                form_values: null
            }));

            var container = document.getElementById('teams-container');
            container.insertBefore(wrapper, container.querySelector('.team-add-column'));
            htmx.process(wrapper);

            teamCount++;
            document.getElementById('team-count').value = teamCount;
        };

        window.removeTeam = function (index) {
            var wrapper = document.getElementById('team-wrapper-' + index);
            if (!wrapper) return;
            // Free assigned users
            wrapper.querySelectorAll('.team-member-chip').forEach(function (chip) {
                var uid = chip.dataset.userId;
                var userItem = document.querySelector('.user-drag-item[data-user-id="' + uid + '"]');
                if (userItem) userItem.classList.remove('user-in-team');
            });
            wrapper.remove();

            teamCount--;
            document.getElementById('team-count').value = teamCount;
        };

        window.removeMember = function (btn, userId) {
            var col = btn.closest('.team-column');
            btn.closest('.team-member-chip').remove();
            // Remove matching hidden input
            var input = col.querySelector('input[data-member-user-id="' + userId + '"]');
            if (input) input.remove();
            // Free user if not in any other team
            if (!document.querySelector('.team-member-chip[data-user-id="' + userId + '"]')) {
                var userItem = document.querySelector('.user-drag-item[data-user-id="' + userId + '"]');
                if (userItem) userItem.classList.remove('user-in-team');
            }
        };
    })();
</script>