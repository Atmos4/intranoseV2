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
            data-team-index="<?= $team_index ?>" hx-post="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_slots"
            hx-target="#slots-<?= $team_index ?>" hx-swap="outerHTML"
            hx-vals='<?= htmlspecialchars(json_encode(["team_index" => $team_index]), ENT_QUOTES) ?>'>
            <?php foreach ($relay_format_options as $val => $label): ?>
                <option value="<?= htmlspecialchars($val) ?>" <?= $val === $team_relay_format ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach ?>
        </select>
    <?php elseif ($current_format): ?>
        <small class="relay-format-label"><i class="fa fa-tag"></i> <?= htmlspecialchars($current_format->name) ?></small>
    <?php endif ?>

    <div hx-post="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_slots" hx-trigger="load" hx-vals='
        <?= htmlspecialchars(json_encode([
            "team_index" => $team_index,
            "team_{$team_index}_relay_format" => $team_relay_format,
            "team_{$team_index}_members" => json_encode(array_map(fn($m) => $m['id'] ?? '', $team_members)),
            "can_edit" => $can_edit
        ]), ENT_QUOTES) ?>' hx-swap="outerHTML">
    </div>

    <div class="team-composition-summary" id="composition-<?= $team_index ?>"></div>
</div>