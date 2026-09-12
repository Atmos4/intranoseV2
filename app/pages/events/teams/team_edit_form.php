<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$pool_id = get_route_param("pool_id");

$can_edit = check_auth(Access::$ADD_EVENTS);

$team_index = get_query_param("team_index") ?? 0;
$team_id = get_query_param("team_id");
$team_name = get_query_param("team_name", numeric: false) ?? "Équipe " . ($team_index + 1);
$team_members = json_decode(get_query_param("team_members", numeric: false) ?? "[]", true);
$team_relay_format = get_query_param("team_relay_format", numeric: false) ?? "";

$team_group = em()->find(TeamGroup::class, $pool_id);
$relay_group_id = $team_group->relay_format;
$relay_format_options = $relay_group_id ? RelayFormatService::formatOptions($relay_group_id) : [];

// Default to first format for new teams
if (!$team_relay_format && !empty($relay_format_options)) {
    $team_relay_format = array_key_first($relay_format_options);
}

$current_format = $team_relay_format ? RelayFormatService::get($team_relay_format) : null;
?>
<div class="team-column" id="team-wrapper-<?= $team_index ?>" data-team-index="<?= $team_index ?>">
    <div class="team-column-header">
        <?php if ($can_edit): ?>
            <input type="text" name="team_<?= $team_index ?>_name" value="<?= e($team_name) ?>"
                placeholder="Nom de l'équipe">
            <button type="button" class="outline secondary" onclick="removeTeam(<?= $team_index ?>)">
                <i class="fa fa-trash"></i>
            </button>
        <?php else: ?>
            <strong>
                <?= e($team_name) ?>
            </strong>
        <?php endif ?>
    </div>
    <?php if ($can_edit): ?>
        <input type="hidden" name="team_<?= $team_index ?>_id" value="<?= e($team_id ?? '') ?>">
    <?php endif ?>

    <?php if ($can_edit && $relay_group_id && count($relay_format_options) > 1):
        # you can use js:{} to reference a live dom value. pretty cool.
        ?>
        <select name="team_<?= $team_index ?>_relay_format" class="team-relay-format-select"
            data-team-index="<?= $team_index ?>" hx-get="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_slots"
            hx-target="#slots-<?= $team_index ?>" hx-swap="outerHTML"
            hx-vals='js:{"team_index": <?= (int) $team_index ?>, "relay_format": event.target.value}'>
            <?php foreach ($relay_format_options as $val => $label): ?>
                <option value="<?= e($val) ?>" <?= $val === $team_relay_format ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach ?>
        </select>
    <?php elseif ($current_format): ?>
        <small class="relay-format-label"><i class="fa fa-tag"></i>
            <?= e($current_format->name) ?>
        </small>
    <?php endif ?>

    <div hx-get="/evenements/<?= $event_id ?>/pool/<?= $pool_id ?>/team_slots" hx-trigger="load" hx-vals='
        <?= htmlspecialchars(json_encode([
            "team_index" => $team_index,
            "relay_format" => $team_relay_format,
            "team_members" => json_encode(array_map(fn($m) => $m['id'] ?? '', $team_members)),
        ]), ENT_QUOTES) ?>' hx-swap="outerHTML">
    </div>

    <div class="team-composition-summary" id="composition-<?= $team_index ?>"></div>
</div>