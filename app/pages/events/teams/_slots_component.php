<?php
restrict_access();

$event_id = get_route_param("event_id");
$pool_id = get_route_param("pool_id");
$team_group = em()->find(TeamGroup::class, $pool_id);

$team_index = $_POST["team_index"] ?? $_GET["team_index"] ?? 0;
$can_edit = $_POST["can_edit"] ?? $_GET["can_edit"] ?? check_auth(Access::$ADD_EVENTS);

$ctx = RelayFormatService::resolveTeamContext($team_index, $team_group);
$current_format = $ctx['current_format'];
$slot_defs      = $ctx['slot_defs'];
$is_ordered     = $ctx['is_ordered'];
$team_members   = $ctx['team_members'];
$legs     = $current_format ? $current_format->getLegs() : [];
$num_slots = $current_format ? $current_format->team_size : 0;
?>

<?php if ($num_slots > 0): ?>
    <div class="team-slots-container" id="slots-<?= $team_index ?>" data-team-index="<?= $team_index ?>">
        <?php for ($i = 0; $i < $num_slots; $i++):
            $slot = $slot_defs[$i] ?? null;
            $member = $team_members[$i] ?? null;
            $leg = $legs[$i] ?? null;
            
            // Determine slot match class for ordered formats
            $slot_match_class = '';
            if ($member && $slot && $is_ordered) {
                if ($slot->sex || $slot->min_category !== null || $slot->max_category !== null) {
                    $slot_match_class = $slot->matches($member['category'] ?? '') ? 'slot-match' : 'slot-mismatch';
                }
            }
        ?>
            <div class="relay-slot <?= $member ? 'relay-slot-filled' : 'relay-slot-empty' ?> <?= $slot_match_class ?>"
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
        <?php if ($can_edit): ?>
            <?php for ($i = 0; $i < $num_slots; $i++): ?>
                <input type="hidden" name="team_<?= $team_index ?>_members[]"
                    value="<?= htmlspecialchars(($team_members[$i]['id'] ?? '')) ?>"
                    data-slot-index="<?= $i ?>">
            <?php endfor ?>
        <?php endif ?>
    </div>
<?php else: ?>
    <div class="team-drop-zone" id="slots-<?= $team_index ?>" data-team-index="<?= $team_index ?>">
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
        <?php if ($can_edit): ?>
            <?php foreach ($team_members as $member): ?>
                <?php if (!$member) continue; ?>
                <input type="hidden" name="team_<?= $team_index ?>_members[]" value="<?= $member['id'] ?>"
                    data-member-user-id="<?= $member['id'] ?>">
            <?php endforeach ?>
        <?php endif ?>
    </div>
<?php endif ?>

<?php
// Include composition as OOB swap (for format dropdown changes)
ob_start();
include __DIR__ . '/_composition_component.php';
$composition_html = ob_get_clean();
// Add hx-swap-oob attribute to the composition div
echo preg_replace('/<div class="team-composition-summary" id="composition-' . $team_index . '">/', 
    '<div class="team-composition-summary" id="composition-' . $team_index . '" hx-swap-oob="true">', 
    $composition_html, 1);
?>
