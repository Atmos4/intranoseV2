<?php
restrict_access();

$event_id = get_route_param("event_id");
$pool_id = get_route_param("pool_id");
$team_group = em()->find(TeamGroup::class, $pool_id);

$team_index = $_POST["team_index"] ?? $_GET["team_index"] ?? 0;

$ctx = RelayFormatService::resolveTeamContext($team_index, $team_group);
$current_format = $ctx['current_format'];
$slot_defs = $ctx['slot_defs'];
$is_ordered = $ctx['is_ordered'];
$member_categories = $ctx['member_categories'];

// Only render for unordered formats with slot constraints
if (!$current_format || $is_ordered || empty($slot_defs)) {
    echo '<div class="team-composition-summary" id="composition-' . $team_index . '"></div>';
    return;
}

// Compute composition validation
$composition_result = RelayFormatService::validateComposition($slot_defs, $member_categories);
$unmet_count = 0;
foreach ($composition_result['slots'] as $slot_filled) {
    if (!$slot_filled)
        $unmet_count++;
}
$extras_count = count($composition_result['extras'] ?? []);
?>

<div class="team-composition-summary" id="composition-<?= $team_index ?>">
    <?php foreach ($slot_defs as $si => $s):
        $is_met = ($composition_result['slots'][$si] ?? null) !== null;
        ?>
        <div class="composition-rule <?= $is_met ? 'composition-met' : '' ?>" data-rule-index="<?= $si ?>">
            <i class="fa <?= $is_met ? 'fa-circle-check' : 'fa-circle' ?> composition-icon"></i>
            <span><?= htmlspecialchars($s->label) ?></span>
            <?php $ct = $s->constraintText();
            if ($ct): ?>
                <small class="composition-constraint">(<?= htmlspecialchars($ct) ?>)</small>
            <?php endif ?>
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