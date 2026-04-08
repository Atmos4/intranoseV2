<?php
restrict_access();
require __DIR__ . "/../../../components/user_card.php";
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
$user_categories = []; // user_id => category name

if ($linked_activity) {
    $activity_entries = ActivityService::getActivityEntries($linked_activity->id);
    foreach ($activity_entries as $entry) {
        if ($entry->present) {
            $registered_users[] = $entry->user;
            if ($entry->category) {
                $user_categories[$entry->user->id] = $entry->category->name;
            }
        }
    }
} else {
    foreach ($all_event_entries as $event_entry) {
        if ($event_entry->present) {
            $registered_users[] = $event_entry->user;
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

    em()->flush();

    Toast::success("Équipes sauvegardées");
    redirect("/evenements/$event_id/pool/$pool_id");
}

page(($team_group->name ?: "Pool #$pool_id") . " - " . $event->name)->css("team_builder.css");
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
    <div class="teams-scroll-container" id="teams-container">
        <?php if (count($existing_teams)):
            foreach ($existing_teams as $index => $team): ?>
                <div id="team-wrapper-<?= $index ?>" hx-post="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_form"
                    hx-trigger="load" hx-swap="outerHTML" hx-vals='<?= htmlspecialchars(json_encode([
                        "action" => $index,
                        "form_values" => [
                            "team_id" => $team->id,
                            "team_name" => $team->name ?: "Équipe " . ($index + 1),
                            "team_relay_format" => $team->relay_format,
                            "team_members" => array_map(fn($m) => [
                                "id" => $m->id,
                                "name" => $m->first_name . ' ' . $m->last_name,
                                "picture" => $m->getPicture(),
                                "category" => $user_categories[$m->id] ?? null,
                            ], $team->members->toArray()),
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

<?php
// Build relay format data for JS
$relay_formats_js = [];
if ($relay_group_id = $team_group->relay_format) {
    foreach (RelayFormatService::byGroup($relay_group_id) as $fmt) {
        $relay_formats_js[$fmt->id] = [
            'team_size' => $fmt->team_size,
            'slots' => array_map(fn($s) => $s->toArray(), $fmt->getSlots()),
        ];
    }
}
?>
<script>
    (function () {
        var teamCount = <?= count($existing_teams) ?>;
        var relayFormats = <?= json_encode($relay_formats_js, JSON_HEX_TAG) ?>;
        var canEdit = <?= $can_edit ? 'true' : 'false' ?>;

        // --- Drag & Drop ---
        if (!canEdit) {
            // Read-only: no drag & drop, no team management
        } else {
            document.getElementById('users-container').addEventListener('dragstart', function (e) {
                var item = e.target.closest('.user-drag-item');
                if (!item) return;
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    id: item.dataset.userId,
                    name: item.dataset.userName,
                    picture: item.dataset.userPicture,
                    category: item.dataset.userCategory || ''
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

                // Enforce max team size from relay format
                var maxMembers = parseInt(zone.dataset.maxMembers) || 0;
                if (maxMembers > 0) {
                    var currentMembers = zone.querySelectorAll('.team-member-chip').length;
                    if (currentMembers >= maxMembers) return;
                }

                // Add member chip
                var chip = document.createElement('div');
                chip.className = 'team-member-chip';
                chip.dataset.userId = userId;
                chip.dataset.userCategory = data.category || '';
                var chipHtml = '<img src="' + data.picture + '" alt="">'
                    + '<span>' + data.name + '</span>';
                if (data.category) {
                    chipHtml += '<small class="user-category-badge">' + data.category + '</small>';
                }
                chipHtml += '<button type="button" onclick="removeMember(this, ' + userId + ')">&times;</button>';
                chip.innerHTML = chipHtml;
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

                updateSlotStatus(zone);
            });
        } // end canEdit drag & drop

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
            var zone = col.querySelector('.team-drop-zone');
            btn.closest('.team-member-chip').remove();
            // Remove matching hidden input
            var input = col.querySelector('input[data-member-user-id="' + userId + '"]');
            if (input) input.remove();
            // Free user if not in any other team
            if (!document.querySelector('.team-member-chip[data-user-id="' + userId + '"]')) {
                var userItem = document.querySelector('.user-drag-item[data-user-id="' + userId + '"]');
                if (userItem) userItem.classList.remove('user-in-team');
            }
            if (zone) updateSlotStatus(zone);
        };

        // --- Slot validation ---
        function parseCategory(cat) {
            var m = (cat || '').match(/^([DH])(\d+)/i);
            return m ? { sex: m[1].toUpperCase(), num: parseInt(m[2]) } : null;
        }

        function slotMatches(slot, cat) {
            var p = parseCategory(cat);
            if (!p) return false;
            if (slot.sex && p.sex !== slot.sex) return false;
            if (slot.min !== null && slot.min !== undefined && p.num < slot.min) return false;
            if (slot.max !== null && slot.max !== undefined && p.num > slot.max) return false;
            return true;
        }

        function slotSpecificity(slot) {
            var s = 0;
            if (slot.sex) s += 4;
            if (slot.max !== null && slot.max !== undefined) s += 2;
            if (slot.min !== null && slot.min !== undefined) s += 1;
            return s;
        }

        function validateSlots(slots, categories) {
            var available = categories.slice();
            var order = slots.map(function (_, i) { return i; });
            order.sort(function (a, b) { return slotSpecificity(slots[b]) - slotSpecificity(slots[a]); });

            var assignments = new Array(slots.length).fill(null);
            order.forEach(function (si) {
                for (var i = 0; i < available.length; i++) {
                    if (available[i] !== null && slotMatches(slots[si], available[i])) {
                        assignments[si] = available[i];
                        available[i] = null;
                        break;
                    }
                }
            });
            var extras = available.filter(function (c) { return c !== null && c !== ''; });
            return { assignments: assignments, extras: extras };
        }

        // --- Format select change handler ---
        if (canEdit) {
            document.getElementById('teams-container').addEventListener('change', function (e) {
                var select = e.target.closest('.team-relay-format-select');
                if (!select) return;
                var col = select.closest('.team-column');
                var zone = col.querySelector('.team-drop-zone');
                if (!zone) return;

                var formatId = select.value;
                var fmt = relayFormats[formatId];
                if (fmt) {
                    zone.dataset.slots = JSON.stringify(fmt.slots);
                    zone.dataset.maxMembers = fmt.team_size;
                } else {
                    zone.dataset.slots = '[]';
                    zone.dataset.maxMembers = '0';
                }

                updateSlotStatus(zone);
            });
        } // end canEdit format handler

        // Re-run slot status after HTMX swaps in team columns
        if (canEdit) {
            document.body.addEventListener('htmx:afterSettle', function (e) {
                var col = e.detail.elt;
                if (!col.classList || !col.classList.contains('team-column')) return;
                var zone = col.querySelector('.team-drop-zone');
                if (zone) updateSlotStatus(zone);
            });
        }

        window.updateSlotStatus = function (zone) {
            if (!canEdit) return;
            var slotsJson = zone.dataset.slots;
            if (!slotsJson || slotsJson === '[]') {
                var existing = zone.closest('.team-column').querySelector('.team-slots-status');
                if (existing) existing.remove();
                return;
            }

            var slots = JSON.parse(slotsJson);
            var categories = [];
            zone.querySelectorAll('.team-member-chip').forEach(function (chip) {
                categories.push(chip.dataset.userCategory || '');
            });

            var result = validateSlots(slots, categories);
            var assignments = result.assignments;
            var extras = result.extras;
            var filled = assignments.filter(function (a) { return a !== null; }).length;

            var col = zone.closest('.team-column');
            var statusDiv = col.querySelector('.team-slots-status');
            if (!statusDiv) {
                statusDiv = document.createElement('div');
                statusDiv.className = 'team-slots-status';
                col.appendChild(statusDiv);
            }

            var hasExtras = extras.length > 0;
            var isComplete = filled === slots.length && !hasExtras;
            var html = '<small class="slots-summary ' + (isComplete ? 'slots-complete' : 'slots-incomplete') + '">'
                + filled + '/' + slots.length + ' postes</small>';

            slots.forEach(function (slot, i) {
                var matched = assignments[i];
                var constraint = (slot.sex || 'D/H') + (slot.min || '') + (slot.max ? '–' + slot.max : (slot.min ? '+' : ''));
                html += '<div class="slot-row ' + (matched ? 'slot-filled' : 'slot-empty') + '">'
                    + '<span class="slot-icon">' + (matched ? '✓' : '○') + '</span>'
                    + '<span class="slot-label">' + slot.label + '</span>'
                    + '<small class="slot-constraint">' + constraint + '</small>'
                    + (matched ? '<small class="slot-match">' + matched + '</small>' : '')
                    + '</div>';
            });

            if (hasExtras) {
                html += '<div class="slot-extras-warning"><i class="fa fa-exclamation-triangle"></i> '
                    + extras.length + ' membre' + (extras.length > 1 ? 's' : '') + ' en trop : '
                    + extras.join(', ') + '</div>';
            }

            statusDiv.innerHTML = html;
        };
    })();
</script>