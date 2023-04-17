<?php
restrict_access();
$can_add_user = check_auth(Access::$EDIT_USERS);
$users_repository = em()->getRepository(User::class);
page("Réactiver des licenciés")->css("user_list.css");
if (!empty($_POST)) {
    foreach (array_keys($_POST) as $key) {
        $user = $users_repository->findOneBy(['id' => $key]);
        $user->active = true;
        em()->persist($user);
    }
    em()->flush();
}
$users = $users_repository->findBy(['active' => "0"], ['last_name' => 'ASC', 'first_name' => 'ASC']);
?>

<form method="post">
    <nav id="page-actions">
        <a href="/licencies" class="secondary"><i class="fas fa-caret-left"></i> Retour</a>
        <div>
            <button type="submit">
                Réactiver
            </button>
        </div>
    </nav>

    <input type="search" id="search-users" placeholder="Rechercher..." onkeyup="searchTable('users-table')">

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
                        <input type="checkbox" id="<?= $user->id ?>" name="<?= $user->id ?>">
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