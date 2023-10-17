<?php
restrict_access(Access::$EDIT_USERS);
page("Réactiver des licenciés")->css("user_list.css");

// Just for convenience
$form = new Validator();

if (isset($_POST['action'])) {
    if (!isset($_POST['selected_users'])) {
        $form->set_error("No users selected");
    } else {
        $dql = "SELECT u FROM User u where u.id IN ("
            . implode(",", array_map(function ($value) {
                if (!is_numeric($value)) {
                    die("Stupide hobbit joufflu !");
                }
                return "?$value";
            }, array_keys($_POST['selected_users']))) . ")";
        $query = em()->createQuery($dql)->setParameters([...$_POST['selected_users']]);
        $users = $query->getResult();

        if ($_POST['action'] === 'reactivate') {
            foreach ($users as $user) {
                // We just want to allow them to login again
                $user->status = UserStatus::INACTIVE;
            }
            em()->flush();
        }
    }
}

$users = UserService::getDeactivatedUserList();

// No user found. Return early
if (!$users): ?>
    <nav id="page-actions">
        <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    </nav>
    <p class="center">Aucun utilisateur désactivé 😴</p>
    <?php
    return;
endif; ?>

<nav id="page-actions">
    <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <button name="action" value="reactivate" form="reactivate-form">Réactiver</button>
</nav>

<input type="search" id="search-users" placeholder="Rechercher..." onkeyup="searchTable('search-users', 'users-table')">

<form method="post" id="reactivate-form">
    <?= $form->render_validation() ?>

    <table id="users-table" class="reactivate">
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <input type="checkbox" id="<?= $user->id ?>" name="selected_users[]" value="<?= $user->id ?>">
                    </td>
                    <td class="lastname">
                        <?= $user->last_name ?>
                    </td>
                    <td class="firstname">
                        <?= $user->first_name ?>
                    </td>
                    <td><a href="/licencies/<?= $user->id ?>/supprimer" class="destructive">
                            Supprimer <i class="fas fa-trash"></i>
                        </a></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</form>

<script src="/assets/js/table-search.js"></script>