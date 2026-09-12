<?php
restrict_access();

$event_id = get_route_param("event_id");
$pool_id = get_route_param("pool_id");
$team_group = em()->find(TeamGroup::class, $pool_id);

$team_relay_format = get_query_param("relay_format", numeric: false);
$member_ids_raw = get_query_param("team_members", numeric: false);
$team_index = get_query_param("team_index");

$ctx = RelayFormatService::resolveTeamContext($team_relay_format, $member_ids_raw, $team_group);
$current_format = $ctx['current_format'];
$slot_defs = $ctx['slot_defs'];
$team_members = $ctx['team_members'];
$is_ordered = $ctx['is_ordered'];

// Only render for unordered formats with slot constraints
if (!$current_format || $is_ordered || empty($slot_defs)) {
    echo '<div class="team-composition-summary" id="composition-' . $team_index . '"></div>';
    return;
}

// Compute composition validation
$composition_result = RelayFormatService::validateComposition($slot_defs, $team_members);
$unmet_count = 0;
foreach ($composition_result['slots'] as $slot_filled) {
    if (!$slot_filled)
        $unmet_count++;
}
$extras_count = count($composition_result['extras'] ?? []);

// Slots generated together (e.g. simpleSlots()) share the same constraint and are
// interchangeable seats, not distinct running positions - group them into one rule
// with a filled/total count instead of numbering them "Relayeur 1/2/3".
$rule_groups = [];
foreach ($slot_defs as $si => $s) {
    $sig = ($s->sex ?? '') . '|' . ($s->min_category ?? '') . '|' . ($s->max_category ?? '');
    if (!isset($rule_groups[$sig])) {
        $ct = $s->constraintText();
        $rule_groups[$sig] = ['label' => $ct !== '' ? $ct : 'Sans contrainte', 'total' => 0, 'filled' => 0];
    }
    $rule_groups[$sig]['total']++;
    if (($composition_result['slots'][$si] ?? null) !== null)
        $rule_groups[$sig]['filled']++;
}
?>

<div class="team-composition-summary" id="composition-<?= $team_index ?>">
    <?php foreach ($rule_groups as $g):
        $is_met = $g['filled'] >= $g['total'];
        ?>
        <div class="composition-rule <?= $is_met ? 'composition-met' : '' ?>">
            <i class="fa <?= $is_met ? 'fa-circle-check' : 'fa-circle' ?> composition-icon"></i>
            <span><?= htmlspecialchars($g['label']) ?></span>
            <small class="composition-constraint">(<?= $g['filled'] ?>/<?= $g['total'] ?>)</small>
        </div>
    <?php endforeach ?>

    <?php if ($unmet_count > 0 || $extras_count > 0): ?>
        <small class="composition-warning">
            <i class="fa fa-triangle-exclamation"></i>
            <?php
            $parts = [];
            if ($unmet_count > 0)
                $parts[] = $unmet_count . ' position(s) de relais sans coureur';
            if ($extras_count > 0)
                $parts[] = $extras_count . ' coureur(s) inadapté(s)';
            echo implode(' · ', $parts);
            ?>
        </small>
    <?php endif ?>
</div>