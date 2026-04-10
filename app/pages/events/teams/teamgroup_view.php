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

<?php
// Build relay format data for JS
$relay_formats_js = [];
if ($relay_group_id = $team_group->relay_format) {
    foreach (RelayFormatService::byGroup($relay_group_id) as $fmt) {
        $relay_formats_js[$fmt->id] = [
            'team_size' => $fmt->team_size,
            'slots' => array_map(fn($s) => $s->toArray(), $fmt->getSlots()),
            'legs' => $fmt->getLegs(),
            'ordered' => $fmt->ordered,
        ];
    }
}
?>
<script>
    (function () {
        var teamCount = <?= count($existing_teams) ?>;
        var relayFormats = <?= json_encode($relay_formats_js, JSON_HEX_TAG) ?>;
        var canEdit = <?= $can_edit ? 'true' : 'false' ?>;
        var container = document.getElementById('teams-container');

        // --- Helpers ---
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

        function slotConstraintLabel(slot) {
            var parts = [];
            if (slot.sex) parts.push(slot.sex);
            if (slot.min !== null && slot.min !== undefined) {
                if (slot.max !== null && slot.max !== undefined) {
                    parts.push(slot.min + '\u2013' + slot.max);
                } else {
                    parts.push(slot.min + '+');
                }
            } else if (slot.max !== null && slot.max !== undefined) {
                parts.push('\u2264' + slot.max);
            }
            return parts.join('');
        }

        function validateTeamComposition(slots, categories) {
            var indices = slots.map(function (_, i) { return i; });
            indices.sort(function (a, b) {
                return slotSpecificity(slots[b]) - slotSpecificity(slots[a]);
            });
            var available = categories.slice();
            var usedCategories = [];
            var matched = {};
            for (var j = 0; j < indices.length; j++) {
                var i = indices[j];
                matched[i] = false;
                for (var k = 0; k < available.length; k++) {
                    if (slotMatches(slots[i], available[k])) {
                        matched[i] = true;
                        usedCategories.push(available[k]);
                        available.splice(k, 1);
                        break;
                    }
                }
            }
            return { slots: matched, unusedCategories: available, usedCategories: usedCategories };
        }

        function updateCompositionStatus(col) {
            var summary = col.querySelector('.team-composition-summary');
            var slotsContainer = col.querySelector('.team-slots-container');

            // Clear all member warnings first
            col.querySelectorAll('.team-member-chip').forEach(function (chip) {
                chip.classList.remove('member-unmatched');
            });

            if (!summary || !slotsContainer) return;
            var slotsData = JSON.parse(slotsContainer.dataset.slots || '[]');
            if (!slotsData.length) return;

            var chips = [];
            col.querySelectorAll('.team-member-chip').forEach(function (chip) {
                chips.push({ el: chip, cat: chip.dataset.userCategory || '' });
            });
            var categories = chips.map(function (c) { return c.cat; }).filter(Boolean);

            var result = validateTeamComposition(slotsData, categories);

            // Update composition rule icons
            summary.querySelectorAll('.composition-rule').forEach(function (rule) {
                var idx = parseInt(rule.dataset.ruleIndex);
                var icon = rule.querySelector('.composition-icon');
                rule.classList.remove('composition-met', 'composition-unmet');
                if (result.slots[idx]) {
                    rule.classList.add('composition-met');
                    if (icon) icon.className = 'fa fa-circle-check composition-icon';
                } else {
                    rule.classList.add('composition-unmet');
                    if (icon) icon.className = 'fa fa-circle composition-icon';
                }
            });

            // Mark unmatched runners
            var unusedCats = result.unusedCategories.slice();
            chips.forEach(function (c) {
                if (!c.cat) {
                    c.el.classList.add('member-unmatched');
                    return;
                }
                var idx = unusedCats.indexOf(c.cat);
                if (idx !== -1) {
                    c.el.classList.add('member-unmatched');
                    unusedCats.splice(idx, 1);
                }
            });

            // Update warning message
            var unmet = 0;
            for (var key in result.slots) { if (!result.slots[key]) unmet++; }
            var extras = result.unusedCategories.length;
            var warnEl = summary.querySelector('.composition-warning');
            if (!warnEl) {
                warnEl = document.createElement('small');
                warnEl.className = 'composition-warning';
                summary.appendChild(warnEl);
            }
            if (unmet || extras) {
                var parts = [];
                if (unmet) parts.push(unmet + ' position(s) de relais sans coureur');
                if (extras) parts.push(extras + ' coureur(s) inadapté(s)');
                warnEl.innerHTML = '<i class="fa fa-triangle-exclamation"></i> ' + parts.join(' · ');
                warnEl.style.display = '';
            } else {
                warnEl.style.display = 'none';
            }
        }

        function createChipHtml(data) {
            return '<img src="' + data.picture + '" alt="">'
                + '<span>' + data.name + '</span>'
                + (data.category ? '<small class="user-category-badge">' + data.category + '</small>' : '')
                + '<button type="button" onclick="removeMember(this, ' + data.id + ')">&times;</button>';
        }

        function refreshUserAssignments() {
            document.querySelectorAll('.user-drag-item').forEach(function (item) {
                var uid = item.dataset.userId;
                var inTeam = document.querySelector('.team-member-chip[data-user-id="' + uid + '"]');
                item.classList.toggle('user-in-team', !!inTeam);
            });
        }

        function syncSlotInputs(col) {
            var slotsContainer = col.querySelector('.team-slots-container');
            if (!slotsContainer) return;
            var teamIndex = slotsContainer.dataset.teamIndex;
            col.querySelectorAll('input[data-slot-index]').forEach(function (el) { el.remove(); });
            slotsContainer.querySelectorAll('.relay-slot-drop').forEach(function (dropArea) {
                var chip = dropArea.querySelector('.team-member-chip');
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'team_' + teamIndex + '_members[]';
                input.value = chip ? chip.dataset.userId : '';
                input.dataset.slotIndex = dropArea.dataset.slotIndex;
                col.appendChild(input);
            });
        }

        function updateSlotMatchStatus(slotEl) {
            var dropArea = slotEl.querySelector('.relay-slot-drop');
            var chip = dropArea ? dropArea.querySelector('.team-member-chip') : null;
            slotEl.classList.remove('slot-match', 'slot-mismatch');
            if (!chip) return;

            var slotsContainer = slotEl.closest('.team-slots-container');
            if (!slotsContainer || slotsContainer.dataset.ordered !== '1') return;

            var slotsData = JSON.parse(slotsContainer.dataset.slots || '[]');
            var slotIndex = parseInt(slotEl.dataset.slotIndex);
            var slotDef = slotsData[slotIndex];
            if (!slotDef) return;
            if (!slotDef.sex && (slotDef.min === null || slotDef.min === undefined) && (slotDef.max === null || slotDef.max === undefined)) return;

            var category = chip.dataset.userCategory || '';
            if (!category) { slotEl.classList.add('slot-mismatch'); return; }
            slotEl.classList.add(slotMatches(slotDef, category) ? 'slot-match' : 'slot-mismatch');
        }

        function clearSlotDrop(dropArea) {
            dropArea.innerHTML = '<span class="slot-drop-hint">Déposer ici</span>';
            var slotEl = dropArea.closest('.relay-slot');
            slotEl.classList.remove('relay-slot-filled', 'slot-match', 'slot-mismatch');
            slotEl.classList.add('relay-slot-empty');
        }

        function fillSlotDrop(dropArea, chipEl) {
            dropArea.innerHTML = '';
            dropArea.appendChild(chipEl);
            var slotEl = dropArea.closest('.relay-slot');
            slotEl.classList.remove('relay-slot-empty');
            slotEl.classList.add('relay-slot-filled');
            updateSlotMatchStatus(slotEl);
        }

        function rebuildSlots(col, formatId) {
            var fmt = relayFormats[formatId];
            var teamIndex = col.dataset.teamIndex;

            var currentMembers = [];
            col.querySelectorAll('.team-member-chip').forEach(function (chip) {
                currentMembers.push({
                    id: chip.dataset.userId,
                    category: chip.dataset.userCategory || '',
                    name: chip.querySelector('span').textContent,
                    picture: chip.querySelector('img').src
                });
            });

            var old = col.querySelector('.team-slots-container') || col.querySelector('.team-drop-zone');
            if (old) old.remove();
            var oldSummary = col.querySelector('.team-composition-summary');
            if (oldSummary) oldSummary.remove();
            col.querySelectorAll('input[data-slot-index]').forEach(function (el) { el.remove(); });
            col.querySelectorAll('input[data-member-user-id]').forEach(function (el) { el.remove(); });

            if (!fmt) {
                // No relay format: show generic drop zone
                var zone = document.createElement('div');
                zone.className = 'team-drop-zone';
                zone.dataset.teamIndex = teamIndex;

                if (currentMembers.length === 0) {
                    zone.innerHTML = '<p class="drop-hint">Déposez des participants ici</p>';
                }

                currentMembers.forEach(function (m) {
                    var chip = document.createElement('div');
                    chip.className = 'team-member-chip';
                    chip.draggable = true;
                    chip.dataset.userId = m.id;
                    chip.dataset.userCategory = m.category;
                    chip.innerHTML = '<div class="member-drag-handle" title="Glisser pour réordonner"><i class="fa fa-grip-vertical"></i></div>' + createChipHtml(m);
                    zone.appendChild(chip);

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'team_' + teamIndex + '_members[]';
                    input.value = m.id;
                    input.dataset.memberUserId = m.id;
                    col.appendChild(input);
                });

                var ref = col.querySelector('.team-relay-format-select') || col.querySelector('.relay-format-label');
                if (ref) {
                    ref.parentNode.insertBefore(zone, ref.nextSibling);
                } else {
                    var hdr = col.querySelector('.team-column-header');
                    hdr.parentNode.insertBefore(zone, hdr.nextSibling);
                }
                refreshUserAssignments();
                return;
            }

            var numSlots = fmt.team_size;
            var slots = fmt.slots || [];
            var legs = fmt.legs || [];
            var isOrdered = !!fmt.ordered;
            var keepMembers = currentMembers.slice(0, numSlots);

            var newContainer = document.createElement('div');
            newContainer.className = 'team-slots-container';
            newContainer.dataset.teamIndex = teamIndex;
            newContainer.dataset.slots = JSON.stringify(slots);
            newContainer.dataset.legs = JSON.stringify(legs);
            newContainer.dataset.ordered = isOrdered ? '1' : '0';

            for (var i = 0; i < numSlots; i++) {
                var slot = slots[i] || null;
                var leg = legs[i] || null;
                var member = keepMembers[i] || null;

                var slotEl = document.createElement('div');
                slotEl.className = 'relay-slot ' + (member ? 'relay-slot-filled' : 'relay-slot-empty');
                slotEl.dataset.slotIndex = i;

                var header = document.createElement('div');
                header.className = 'relay-slot-header';
                var hHtml = '<span class="relay-slot-position">' + (i + 1) + '</span>';
                if (leg) hHtml += '<span class="relay-slot-duration">' + leg + "'" + '</span>';
                if (slot && isOrdered) hHtml += '<small class="relay-slot-label">' + slot.label + '</small>';
                header.innerHTML = hHtml;
                slotEl.appendChild(header);

                var dropArea = document.createElement('div');
                dropArea.className = 'relay-slot-drop';
                dropArea.dataset.slotIndex = i;
                if (member) {
                    var chip = document.createElement('div');
                    chip.className = 'team-member-chip';
                    chip.draggable = true;
                    chip.dataset.userId = member.id;
                    chip.dataset.userCategory = member.category;
                    chip.innerHTML = createChipHtml(member);
                    dropArea.appendChild(chip);
                } else {
                    dropArea.innerHTML = '<span class="slot-drop-hint">Déposer ici</span>';
                }
                slotEl.appendChild(dropArea);
                newContainer.appendChild(slotEl);
            }

            var ref = col.querySelector('.team-relay-format-select') || col.querySelector('.relay-format-label');
            if (ref) {
                ref.parentNode.insertBefore(newContainer, ref.nextSibling);
            } else {
                var header = col.querySelector('.team-column-header');
                header.parentNode.insertBefore(newContainer, header.nextSibling);
            }

            syncSlotInputs(col);
            newContainer.querySelectorAll('.relay-slot').forEach(function (s) {
                updateSlotMatchStatus(s);
            });

            // Build composition summary for unordered formats
            if (!isOrdered && slots.length > 0) {
                var summaryDiv = document.createElement('div');
                summaryDiv.className = 'team-composition-summary';
                for (var si = 0; si < slots.length; si++) {
                    var rule = document.createElement('div');
                    rule.className = 'composition-rule';
                    rule.dataset.ruleIndex = si;
                    var ct = slotConstraintLabel(slots[si]);
                    rule.innerHTML = '<i class="fa fa-circle composition-icon"></i>'
                        + '<span>' + (slots[si].label || 'Coureur') + '</span>'
                        + (ct ? '<small class="composition-constraint">(' + ct + ')</small>' : '');
                    summaryDiv.appendChild(rule);
                }
                var warnEl = document.createElement('small');
                warnEl.className = 'composition-warning';
                warnEl.style.display = 'none';
                summaryDiv.appendChild(warnEl);
                newContainer.parentNode.insertBefore(summaryDiv, newContainer.nextSibling);
            }

            updateCompositionStatus(col);
            refreshUserAssignments();
        }

        // --- Drag & Drop (editor mode) ---
        if (canEdit) {
            var draggedChip = null;
            var dragSourceSlot = null;
            var dragFromHandle = false;

            // Track mousedown on drag handle (dragstart e.target is always the draggable, not the click origin)
            container.addEventListener('mousedown', function (e) {
                dragFromHandle = !!e.target.closest('.member-drag-handle');
            });

            // User panel dragstart
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

            // Member chip dragstart
            container.addEventListener('dragstart', function (e) {
                var chip = e.target.closest('.team-member-chip');
                if (!chip) return;
                var slotDrop = chip.closest('.relay-slot-drop');
                if (slotDrop) {
                    draggedChip = chip;
                    dragSourceSlot = slotDrop;
                    chip.classList.add('member-dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/member-swap', chip.dataset.userId);
                } else {
                    if (!dragFromHandle) { e.preventDefault(); return; }
                    draggedChip = chip;
                    dragSourceSlot = null;
                    chip.classList.add('member-dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/member-reorder', chip.dataset.userId);
                }
            });

            // Dragover
            container.addEventListener('dragover', function (e) {
                var slotDrop = e.target.closest('.relay-slot-drop');
                if (slotDrop) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    slotDrop.classList.add('drag-over');
                    return;
                }
                var zone = e.target.closest('.team-drop-zone');
                if (zone) {
                    if (draggedChip && dragSourceSlot) return;
                    if (draggedChip) {
                        var target = e.target.closest('.team-member-chip');
                        if (!target || target === draggedChip || !zone.contains(target)) return;
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        var rect = target.getBoundingClientRect();
                        if (e.clientY > rect.top + rect.height / 2) {
                            target.parentNode.insertBefore(draggedChip, target.nextSibling);
                        } else {
                            target.parentNode.insertBefore(draggedChip, target);
                        }
                    } else {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        zone.classList.add('drag-over');
                    }
                }
            });

            // Dragleave
            container.addEventListener('dragleave', function (e) {
                var slotDrop = e.target.closest('.relay-slot-drop');
                if (slotDrop && !slotDrop.contains(e.relatedTarget)) {
                    slotDrop.classList.remove('drag-over');
                }
                var zone = e.target.closest('.team-drop-zone');
                if (zone && !zone.contains(e.relatedTarget)) {
                    zone.classList.remove('drag-over');
                }
            });

            // Drop
            container.addEventListener('drop', function (e) {
                // --- Relay slot drop ---
                var slotDrop = e.target.closest('.relay-slot-drop');
                if (slotDrop) {
                    slotDrop.classList.remove('drag-over');

                    if (draggedChip && dragSourceSlot) {
                        e.preventDefault();
                        if (dragSourceSlot === slotDrop) return;
                        var sourceChip = dragSourceSlot.querySelector('.team-member-chip');
                        var targetChip = slotDrop.querySelector('.team-member-chip');
                        var sourceCol = dragSourceSlot.closest('.team-column');
                        var targetCol = slotDrop.closest('.team-column');

                        if (sourceChip) sourceChip.remove();
                        if (targetChip) targetChip.remove();

                        if (sourceChip) fillSlotDrop(slotDrop, sourceChip);
                        else clearSlotDrop(slotDrop);

                        if (targetChip) fillSlotDrop(dragSourceSlot, targetChip);
                        else clearSlotDrop(dragSourceSlot);

                        syncSlotInputs(sourceCol);
                        if (targetCol !== sourceCol) syncSlotInputs(targetCol);
                        updateCompositionStatus(sourceCol);
                        if (targetCol !== sourceCol) updateCompositionStatus(targetCol);
                        refreshUserAssignments();
                        return;
                    }

                    if (e.dataTransfer.types.indexOf('text/plain') === -1) return;
                    e.preventDefault();
                    var data = JSON.parse(e.dataTransfer.getData('text/plain'));
                    var userId = data.id;
                    var col = slotDrop.closest('.team-column');

                    document.querySelectorAll('.team-member-chip[data-user-id="' + userId + '"]').forEach(function (chip) {
                        var existingSlot = chip.closest('.relay-slot-drop');
                        var existingCol = chip.closest('.team-column');
                        if (existingSlot) {
                            clearSlotDrop(existingSlot);
                            syncSlotInputs(existingCol);
                            updateCompositionStatus(existingCol);
                        } else {
                            chip.remove();
                            var inp = existingCol.querySelector('input[data-member-user-id="' + userId + '"]');
                            if (inp) inp.remove();
                        }
                    });

                    var existing = slotDrop.querySelector('.team-member-chip');
                    if (existing) existing.remove();

                    var chip = document.createElement('div');
                    chip.className = 'team-member-chip';
                    chip.draggable = true;
                    chip.dataset.userId = userId;
                    chip.dataset.userCategory = data.category || '';
                    chip.innerHTML = createChipHtml(data);

                    fillSlotDrop(slotDrop, chip);
                    syncSlotInputs(col);
                    updateCompositionStatus(col);
                    refreshUserAssignments();
                    return;
                }

                // --- Generic drop zone (non-relay fallback) ---
                var zone = e.target.closest('.team-drop-zone');
                if (!zone) return;
                if (e.dataTransfer.types.indexOf('text/member-reorder') !== -1) return;
                if (draggedChip) return;
                e.preventDefault();
                zone.classList.remove('drag-over');

                var data = JSON.parse(e.dataTransfer.getData('text/plain'));
                var userId = data.id;
                var teamIndex = zone.dataset.teamIndex;
                document.querySelectorAll('.team-member-chip[data-user-id="' + userId + '"]').forEach(function (el) { el.remove(); });
                document.querySelectorAll('input[data-member-user-id="' + userId + '"]').forEach(function (el) { el.remove(); });
                if (zone.querySelector('.team-member-chip[data-user-id="' + userId + '"]')) return;

                var hint = zone.querySelector('.drop-hint');
                if (hint) hint.remove();

                var chip = document.createElement('div');
                chip.className = 'team-member-chip';
                chip.draggable = true;
                chip.dataset.userId = userId;
                chip.dataset.userCategory = data.category || '';
                chip.innerHTML = '<div class="member-drag-handle"><i class="fa fa-grip-vertical"></i></div>' + createChipHtml(data);
                zone.appendChild(chip);

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'team_' + teamIndex + '_members[]';
                input.value = userId;
                input.dataset.memberUserId = userId;
                zone.closest('.team-column').appendChild(input);

                refreshUserAssignments();
            });

            // Dragend
            container.addEventListener('dragend', function (e) {
                if (!draggedChip) return;
                draggedChip.classList.remove('member-dragging');
                if (!dragSourceSlot) {
                    var zone = draggedChip.closest('.team-drop-zone');
                    if (zone) {
                        var col = zone.closest('.team-column');
                        var teamIndex = zone.dataset.teamIndex;
                        col.querySelectorAll('input[data-member-user-id]').forEach(function (el) { el.remove(); });
                        zone.querySelectorAll('.team-member-chip').forEach(function (c) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'team_' + teamIndex + '_members[]';
                            input.value = c.dataset.userId;
                            input.dataset.memberUserId = c.dataset.userId;
                            col.appendChild(input);
                        });
                    }
                }
                draggedChip = null;
                dragSourceSlot = null;
            });

            // Format change
            container.addEventListener('change', function (e) {
                var select = e.target.closest('.team-relay-format-select');
                if (!select) return;
                var col = select.closest('.team-column');
                rebuildSlots(col, select.value);
            });
        } // end canEdit

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

            container.insertBefore(wrapper, container.querySelector('.team-add-column'));
            htmx.process(wrapper);
            teamCount++;
            document.getElementById('team-count').value = teamCount;
        };

        window.removeTeam = function (index) {
            var wrapper = document.getElementById('team-wrapper-' + index);
            if (!wrapper) return;
            wrapper.remove();
            teamCount--;
            document.getElementById('team-count').value = teamCount;
            refreshUserAssignments();
        };

        window.removeMember = function (btn, userId) {
            var chip = btn.closest('.team-member-chip');
            var col = chip.closest('.team-column');
            var slotDrop = chip.closest('.relay-slot-drop');

            if (slotDrop) {
                clearSlotDrop(slotDrop);
                syncSlotInputs(col);
                updateCompositionStatus(col);
            } else {
                chip.remove();
                var input = col.querySelector('input[data-member-user-id="' + userId + '"]');
                if (input) input.remove();
            }
            refreshUserAssignments();
        };

        // --- HTMX after settle ---
        document.body.addEventListener('htmx:afterSettle', function (e) {
            var col = e.detail.elt;
            if (!col.classList || !col.classList.contains('team-column')) return;
            col.querySelectorAll('.relay-slot').forEach(function (s) {
                updateSlotMatchStatus(s);
            });
            updateCompositionStatus(col);
            refreshUserAssignments();
        });

        // --- Init ---
        document.querySelectorAll('.relay-slot').forEach(function (s) {
            updateSlotMatchStatus(s);
        });
        document.querySelectorAll('.team-column').forEach(function (col) {
            updateCompositionStatus(col);
        });
    })();
</script>