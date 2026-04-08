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

$current_format = $team_relay_format ? RelayFormatService::get($team_relay_format) : null;
$slots = $current_format ? $current_format->getSlots() : [];
$member_categories = array_filter(array_map(fn($m) => $m['category'] ?? null, $team_members));
$slot_validation = !empty($slots) ? RelayFormatService::validateComposition($slots, $member_categories) : null;
$slots_json = !empty($slots) ? json_encode(array_map(fn($s) => $s->toArray(), $slots)) : '[]';
?>
<div class="team-column" id="team-wrapper-<?= $team_index ?>">
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

    <div class="team-drop-zone" data-team-index="<?= $team_index ?>"
        data-max-members="<?= $current_format ? $current_format->team_size : 0 ?>"
        data-slots='<?= htmlspecialchars($slots_json, ENT_QUOTES) ?>'>
        <?php if (empty($team_members)): ?>
            <p class="drop-hint">Déposez des participants ici</p>
        <?php endif ?>
        <?php foreach ($team_members as $member): ?>
            <div class="team-member-chip" data-user-id="<?= $member['id'] ?>"
                data-user-category="<?= htmlspecialchars($member['category'] ?? '') ?>">
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

    <?php if ($can_edit && $slot_validation): ?>
        <div class="team-slots-status" data-team-index="<?= $team_index ?>">
            <small
                class="slots-summary <?= $slot_validation['filled'] === $slot_validation['total'] ? 'slots-complete' : 'slots-incomplete' ?>">
                <?= $slot_validation['filled'] ?>/<?= $slot_validation['total'] ?> postes
            </small>
            <?php foreach ($slots as $i => $slot): ?>
                <div class="slot-row <?= $slot_validation['slots'][$i] !== null ? 'slot-filled' : 'slot-empty' ?>">
                    <span class="slot-icon"><?= $slot_validation['slots'][$i] !== null ? '✓' : '○' ?></span>
                    <span class="slot-label"><?= htmlspecialchars($slot->label) ?></span>
                    <?php if ($slot->sex || $slot->min_category): ?>
                        <small class="slot-constraint">
                            <?= $slot->sex ? $slot->sex : 'D/H' ?>             <?= $slot->min_category ?? '' ?>
                            <?= $slot->max_category ? '–' . $slot->max_category : ($slot->min_category ? '+' : '') ?>
                        </small>
                    <?php endif ?>
                    <?php if ($slot_validation['slots'][$i]): ?>
                        <small class="slot-match"><?= htmlspecialchars($slot_validation['slots'][$i]) ?></small>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
            <?php if (!empty($slot_validation['extras'])): ?>
                <div class="slot-extras-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?= count($slot_validation['extras']) ?> membre<?= count($slot_validation['extras']) > 1 ? 's' : '' ?> en
                    trop :
                    <?= htmlspecialchars(implode(', ', $slot_validation['extras'])) ?>
                </div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ($can_edit): ?>
        <?php foreach ($team_members as $member): ?>
            <input type="hidden" name="team_<?= $team_index ?>_members[]" value="<?= $member['id'] ?>"
                data-member-user-id="<?= $member['id'] ?>">
        <?php endforeach ?>
    <?php endif ?>
</div>