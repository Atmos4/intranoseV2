<?php
require_once "database/licencie_data.php";
page("Les licenciés");
check_auth("USER");
$licencies = get_all_licencies();
?>
<main class="container">
    <form method="get">
        <input type="search" id="search-users" name="search" placeholder="Rechercher..." onkeyup="searchTable('search-users','users-table')">
    </form>
    <figure>
        <table role="grid" id="users-table">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Prénom</th>
                    <th scope="col">Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licencies as $user) : ?>
                    <tr style="cursor:pointer" onclick="window.location.href = '/les-licencies/details/<?= $user['id'] ?>'">
                        <td><?= $user['nom'] ?></td>
                        <td><?= $user['prenom'] ?></td>
                        <td><?= $user['email'] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </figure>
</main>

<script src="/assets/js/table-search.js"></script>