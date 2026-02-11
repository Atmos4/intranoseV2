<?php
restrict_access(Access::$EDIT_USERS);
page("Licenciés inactifs");

$users = UserService::getInactiveUserList();

$actions = actions()->back("/licencies");
?>

<?= $actions ?>

<?php if (!$users): ?>
    <p class="center">Aucun utilisateur inactif 😴</p>
    <?php
    return;
endif; ?>


<figure class="overflow-auto">
    <table id="users-table">
        <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?= $user->last_name ?>
                    </td>
                    <td>
                        <?= $user->first_name ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</figure>