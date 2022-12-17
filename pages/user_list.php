<?php
restrict_access();

require_once "database/users.api.php";
$users = get_all_users();

page("Les licenciés");
?>
<form method="get">
    <input type="search" id="search-users" name="search" placeholder="Rechercher..." onkeyup="searchTable('search-users','users-table')">
</form>
<figure>
    <table id="users-table">
        <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th scope="col">Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <tr style="cursor:pointer" onclick="window.location.href = '/licencies/<?= $user['id'] ?>'">
                    <td><?= $user['nom'] ?></td>
                    <td><?= $user['prenom'] ?></td>
                    <td><?= $user['email'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</figure>

<script src="/assets/js/table-search.js"></script>