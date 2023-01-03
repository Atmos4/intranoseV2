<?php
restrict_access();

require_once "database/users.api.php";
$users = get_all_users();

page("Les licenciés", "user_list.css");
?>
<form method="get">
    <input type="search" id="search-users" name="search" placeholder="Rechercher..."
        onkeyup="searchTable('search-users','users-table')">
</form>
<table id="users-table">
    <thead>
        <tr>
            <th scope="col">Nom</th>
            <th scope="col">Prénom</th>
            <th scope="col">Email</th>
            <th scope="col">Portable</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr class="clickable" onclick="window.location.href = '/licencies/<?= $user['id'] ?>'">
                <td class="lastname">
                    <?= $user['nom'] ?>
                </td>
                <td class="firstname">
                    <?= $user['prenom'] ?>
                </td>
                <td class="email"><?= $user['email'] ?></td>
                <td class="phone-number">
                    <?= $user['telport'] == "" ? $user['tel'] : $user['telport'] ?>
                </td>
            </tr>
            <?php endforeach ?>
    </tbody>
</table>

<script src="/assets/js/table-search.js"></script>