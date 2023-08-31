<?php
restrict_access();
$can_add_user = check_auth(Access::$EDIT_USERS);
$users_repository = em()->getRepository(User::class);
page("Réactiver des licenciés")->css("user_list.css");

if (isset($_POST['action'])) {
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
            $user->active = true;
        }
        em()->flush();
    } elseif ($_POST['action'] === 'delete') {
        foreach ($users as $user) {
            em()->remove($user);
        }
        em()->flush();
    }
}
$users = $users_repository->findBy(['active' => "0"], ['last_name' => 'ASC', 'first_name' => 'ASC']);
?>

<nav id="page-actions">
    <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
    <li>
        <details role="list" dir="rtl">
            <summary role="link" aria-haspopup="listbox" class="contrast">Actions</summary>
            <ul role="listbox">
                <li><button type="submit" name="action" class="error" value="reactivate" form="deactivate-form">
                        Réactiver
                    </button></li>
                <li>
                    <button type="submit" name="action" value="delete" form="deactivate-form">
                        Supprimer
                    </button>
                </li>
            </ul>
        </details>
    </li>
</nav>

<input type="search" id="search-users" placeholder="Rechercher..." onkeyup="searchTable('search-users', 'users-table')">

<form method="post" id="deactivate-form">

    <table id="users-table" class="reactivate">
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
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
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</form>

<script src="/assets/js/table-search.js"></script>