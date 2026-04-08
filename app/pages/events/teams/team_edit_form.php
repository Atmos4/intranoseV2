<?php
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id");
$pool_id = get_route_param("pool_id");

$post_form_values = isset($_POST["form_values"]) ? (is_string($_POST["form_values"]) ? json_decode($_POST["form_values"], true) : $_POST["form_values"]) : null;
$team_index = $_POST["action"] ?? 0;

$team_id = $post_form_values["team_id"] ?? null;
$team_name = $post_form_values["team_name"] ?? "Équipe " . ($team_index + 1);
$team_members = $post_form_values["team_members"] ?? [];
?>
<div class="team-column" id="team-wrapper-<?= $team_index ?>">
    <div class="team-column-header">
        <input type="text" name="team_<?= $team_index ?>_name" value="<?= htmlspecialchars($team_name) ?>"
            placeholder="Nom de l'équipe">
        <button type="button" class="outline secondary" onclick="removeTeam(<?= $team_index ?>)">
            <i class="fa fa-trash"></i>
        </button>
    </div>
    <input type="hidden" name="team_<?= $team_index ?>_id" value="<?= htmlspecialchars($team_id ?? '') ?>">

    <div class="team-drop-zone" data-team-index="<?= $team_index ?>">
        <?php if (empty($team_members)): ?>
            <p class="drop-hint">Déposez des participants ici</p>
        <?php endif ?>
        <?php foreach ($team_members as $member): ?>
            <div class="team-member-chip" data-user-id="<?= $member['id'] ?>">
                <img src="<?= htmlspecialchars($member['picture']) ?>" alt="">
                <span><?= htmlspecialchars($member['name']) ?></span>
                <?php if (!empty($member['category'])): ?>
                    <small class="user-category-badge"><?= htmlspecialchars($member['category']) ?></small>
                <?php endif ?>
                <button type="button" onclick="removeMember(this, <?= $member['id'] ?>)">&times;</button>
            </div>
        <?php endforeach ?>
    </div>

    <?php foreach ($team_members as $member): ?>
        <input type="hidden" name="team_<?= $team_index ?>_members[]" value="<?= $member['id'] ?>"
            data-member-user-id="<?= $member['id'] ?>">
    <?php endforeach ?>
</div>