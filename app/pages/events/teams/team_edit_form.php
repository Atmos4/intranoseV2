<?php
restrict_access();

$event_id = get_route_param("event_id");
$pool_id = get_route_param("pool_id");

$team_group = em()->find(TeamGroup::class, $pool_id);
$relay_group_id = $team_group->relay_format;
$relay_format_options = $relay_group_id ? RelayFormatService::formatOptions($relay_group_id) : [];

$post_form_values = isset($_POST["form_values"]) ? (is_string($_POST["form_values"]) ? json_decode($_POST["form_values"], true) : $_POST["form_values"]) : null;
$team_index = $_POST["action"] ?? 0;

$team_id = $post_form_values["team_id"] ?? null;
$team_name = $post_form_values["team_name"] ?? "Équipe " . ($team_index + 1);
$team_members = $post_form_values["team_members"] ?? [];
$team_relay_format = $post_form_values["team_relay_format"] ?? "";
$can_edit = $post_form_values["can_edit"] ?? check_auth(Access::$ADD_EVENTS);

// Default to first format for new teams
if (!$team_relay_format && !empty($relay_format_options)) {
    $team_relay_format = array_key_first($relay_format_options);
}

$current_format = $team_relay_format ? RelayFormatService::get($team_relay_format) : null;
$slot_defs = $current_format ? $current_format->getSlots() : [];
$legs = $current_format ? $current_format->getLegs() : [];
$num_slots = $current_format ? $current_format->team_size : 0;
$slots_json = json_encode(array_map(fn($s) => $s->toArray(), $slot_defs));
$legs_json = json_encode($legs);
$is_ordered = $current_format ? $current_format->ordered : false;

// Compute composition status for initial render (unordered formats only)
$composition_result = null;
if ($current_format && !$is_ordered && !empty($slot_defs)) {
    $member_categories = array_filter(array_map(fn($m) => $m['category'] ?? null, $team_members));
    $composition_result = RelayFormatService::validateComposition($slot_defs, array_values($member_categories));
}
?>
<div class="team-column" id="team-wrapper-<?= $team_index ?>" data-team-index="<?= $team_index ?>">
    <div class="team-column-header">
        <?php if ($can_edit): ?>
            <input type="text" name="team_<?= $team_index ?>_name" value="<?= htmlspecialchars($team_name) ?>"
                placeholder="Nom de l'équipe">
            <button type="button" class="outline secondary" onclick="removeTeam(<?= $team_index ?>)">
                <i class="fa fa-trash"></i>
            </button>
        <?php else: ?>
            <strong><?= htmlspecialchars($team_name) ?></strong>
        <?php endif ?>
    </div>
    <?php if ($can_edit): ?>
        <input type="hidden" name="team_<?= $team_index ?>_id" value="<?= htmlspecialchars($team_id ?? '') ?>">
    <?php endif ?>

    <?php if ($can_edit && $relay_group_id && count($relay_format_options) > 1): ?>
        <select name="team_<?= $team_index ?>_relay_format" class="team-relay-format-select"
            data-team-index="<?= $team_index ?>">
            <?php foreach ($relay_format_options as $val => $label): ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= $val === $team_relay_format ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach ?>
        </select>
    <?php elseif ($current_format): ?>
        <small class="relay-format-label"><i class="fa fa-tag"></i> <?= htmlspecialchars($current_format->name) ?></small>
    <?php endif ?>

    <?php if ($num_slots > 0): ?>
        <div class="team-slots-container" data-team-index="<?= $team_index ?>"
            data-slots='<?= htmlspecialchars($slots_json, ENT_QUOTES) ?>'
            data-legs='<?= htmlspecialchars($legs_json, ENT_QUOTES) ?>'
            data-ordered="<?= $is_ordered ? '1' : '0' ?>">
            <?php for ($i = 0; $i < $num_slots; $i++):
                $slot = $slot_defs[$i] ?? null;
                $member = $team_members[$i] ?? null;
                $leg = $legs[$i] ?? null;
            ?>
                <div class="relay-slot <?= $member ? 'relay-slot-filled' : 'relay-slot-empty' ?>"
                    data-slot-index="<?= $i ?>">
                    <div class="relay-slot-header">
                        <span class="relay-slot-position"><?= $i + 1 ?></span>
                        <?php if ($leg): ?>
                            <span class="relay-slot-duration"><?= $leg ?>'</span>
                        <?php endif ?>
                        <?php if ($slot && $is_ordered): ?>
                            <small class="relay-slot-label"><?= htmlspecialchars($slot->label) ?></small>
                        <?php endif ?>
                    </div>
                    <div class="relay-slot-drop" data-slot-index="<?= $i ?>">
                        <?php if ($member): ?>
                            <div class="team-member-chip" data-user-id="<?= $member['id'] ?>"
                                data-user-category="<?= htmlspecialchars($member['category'] ?? '') ?>"
                                <?= $can_edit ? 'draggable="true"' : '' ?>>
                                <img src="<?= htmlspecialchars($member['picture']) ?>" alt="">
                                <span><?= htmlspecialchars($member['name']) ?></span>
                                <?php if (!empty($member['category'])): ?>
                                    <small class="user-category-badge"><?= htmlspecialchars($member['category']) ?></small>
                                <?php endif ?>
                                <?php if ($can_edit): ?>
                                    <button type="button" onclick="removeMember(this, <?= $member['id'] ?>)">&times;</button>
                                <?php endif ?>
                            </div>
                        <?php elseif ($can_edit): ?>
                            <span class="slot-drop-hint">Déposer ici</span>
                        <?php else: ?>
                            <span class="slot-drop-hint">—</span>
                        <?php endif ?>
                    </div>
                </div>
            <?php endfor ?>
        </div>
        <?php if ($composition_result !== null): ?>
            <div class="team-composition-summary">
                <?php foreach ($slot_defs as $si => $s):
                    $is_met = ($composition_result['slots'][$si] ?? null) !== null;
                ?>
                    <div class="composition-rule <?= $is_met ? 'composition-met' : '' ?>" data-rule-index="<?= $si ?>">
                        <i class="fa <?= $is_met ? 'fa-circle-check' : 'fa-circle' ?> composition-icon"></i>
                        <span><?= htmlspecialchars($s->label) ?></span>
                        <?php $ct = $s->constraintText(); if ($ct): ?>
                            <small class="composition-constraint">(<?= htmlspecialchars($ct) ?>)</small>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="team-drop-zone" data-team-index="<?= $team_index ?>">
            <?php if (empty(array_filter($team_members))): ?>
                <p class="drop-hint">Déposez des participants ici</p>
            <?php endif ?>
            <?php foreach ($team_members as $member): ?>
                <?php if (!$member) continue; ?>
                <div class="team-member-chip" data-user-id="<?= $member['id'] ?>"
                    data-user-category="<?= htmlspecialchars($member['category'] ?? '') ?>"
                    <?= $can_edit ? 'draggable="true"' : '' ?>>
                    <?php if ($can_edit): ?>
                        <div class="member-drag-handle" title="Glisser pour réordonner"><i class="fa fa-grip-vertical"></i></div>
                    <?php endif ?>
                    <img src="<?= htmlspecialchars($member['picture']) ?>" alt="">
                    <span><?= htmlspecialchars($member['name']) ?></span>
                    <?php if (!empty($member['category'])): ?>
                        <small class="user-category-badge"><?= htmlspecialchars($member['category']) ?></small>
                    <?php endif ?>
                    <?php if ($can_edit): ?>
                        <button type="button" onclick="removeMember(this, <?= $member['id'] ?>)">&times;</button>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php if ($can_edit): ?>
        <?php if ($num_slots > 0): ?>
            <?php for ($i = 0; $i < $num_slots; $i++): ?>
                <input type="hidden" name="team_<?= $team_index ?>_members[]"
                    value="<?= htmlspecialchars(($team_members[$i]['id'] ?? '')) ?>"
                    data-slot-index="<?= $i ?>">
            <?php endfor ?>
        <?php else: ?>
            <?php foreach ($team_members as $member): ?>
                <?php if (!$member) continue; ?>
                <input type="hidden" name="team_<?= $team_index ?>_members[]" value="<?= $member['id'] ?>"
                    data-member-user-id="<?= $member['id'] ?>">
            <?php endforeach ?>
        <?php endif ?>
    <?php endif ?>
</div>