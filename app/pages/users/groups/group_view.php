<?php
require __DIR__ . "/../../../components/user_card.php";
$user = User::getMain();
$can_edit_user = check_auth(Access::$EDIT_USERS);
$group = em()->find(UserGroup::class, get_route_param("group_id"));
if (!$group) {
    force_404("this group does not exist");
}
if ($can_edit_user && isset($_POST['add_members']) && count($_POST['add_members'])) {
    // Fetch all users to be added
    $users = UserService::getFromList($_POST['add_members']);
    // Add each user to the group
    foreach ($users as $user) {
        if (!$user->groups->contains($group)) {
            $user->groups->add($group);
            em()->persist($user);
        }
    }
    em()->flush();
}
$add_member_list = GroupService::getAvailableMembers($group);

$actions = actions()->back("/groupes");
if ($can_edit_user) {
    $actions->dropdown(
        fn($b) => $b
            ->link(
                "/groupes/$group->id/supprimer",
                "Supprimer le groupe",
                "fa fa-trash",
                ["class" => "destructive outline"]
            )
    );
}

page("Groupe : " . $group->name)->css("group_view.css");

?>

<?= $actions ?>

<sl-tab-group>
    <sl-tab slot="nav" panel="members" id="members-tab" data-intro="Cet onglet contient les membres du groupe">
        Membres
    </sl-tab>
    <sl-tab slot="nav" panel="messages" id="messages-tab" hx-trigger="load"
        hx-post="/groupes/<?= $group->id ?>/messages" hx-target="#messages">
        Messages
    </sl-tab>

    <sl-tab-panel name="members">
        <div>

            <div class="row">
                <?php foreach ($group->members as $key => $g_member): ?>
                    <div class="col-sm-12 col-md-6">
                        <?php UserCard(
                            user: $g_member,
                            actions: function ($g_member) use ($group) { ?>
                            <nav>
                                <ul>
                                    <li>
                                        <?php if (check_auth(Access::$EDIT_USERS)): ?>
                                            <details class="dropdown" dir="rtl">
                                                <summary aria-haspopup="listbox" class="contrast actions">
                                                    <i class="fa fa-ellipsis-vertical"></i>
                                                </summary>
                                                <ul dir="rtl">
                                                    <li><a href="<?= "/groupes/$group->id/retirer/$g_member->id" ?>"
                                                            class="destructive">
                                                            Retirer
                                                            <i class="fa fa-xmark"></i>
                                                        </a></li>
                                                </ul>
                                            </details>
                                        <?php endif ?>
                                    </li>
                                </ul>
                            </nav>
                            <?php
                                                    },
                        ) ?>
                    </div>
                <?php endforeach ?>
            </div>

            <?php if ($can_edit_user): ?>
                <form method="post">
                    <input type="hidden" name="action" value="add_members">
                    <h4>Ajouter un membre</h4>
                    <details class="dropdown">
                        <summary aria-haspopup="listbox" data-intro="Ajoutez de nouveaux membres au groupe">Ajouter au
                            groupe...
                        </summary>
                        <ul data-placement=top>
                            <?php foreach ($add_member_list as $add_member): ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="add_members[]" value="<?= $add_member['id'] ?>">
                                        <?= "{$add_member['last_name']} {$add_member['first_name']}" ?>
                                    </label>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </details>
                    <button type="submit">Ajouter</button>
                </form>
                <?php import(__DIR__ . '/group_edit.php') ?>
            <?php endif ?>
        </div>
    </sl-tab-panel>
    <sl-tab-panel name="messages" id="messages">

    </sl-tab-panel>


    <?= UserModal::renderRoot() ?>